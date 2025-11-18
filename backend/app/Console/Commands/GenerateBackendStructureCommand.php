<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Backend\FileGenerator;
use App\Console\Commands\Backend\MigrationParser;
use App\Console\Commands\Backend\TemplateDataGenerator;
use Illuminate\Console\Command;

final class GenerateBackendStructureCommand extends Command
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    protected $signature = 'generate:backend
    {module : Module name (e.g., Employees)}
    {entity : Feature name (e.g., SalaryProgram)}';

    protected $description = 'Generate modular structure files (DTO, FormRequests, Resource, Model, UseCases, Repositories, Service, Controller, Routes) with CRUD operations for a single feature from a migration file.';

    public function handle(): int
    {
        $module = $this->argument('module');
        $entity = $this->argument('entity');

        $this->info("Detecting migration file for entity: {$entity}...");

        $migrationPath = $this->findMigrationPath($entity);

        if (!$migrationPath || !file_exists($migrationPath)) {
            $this->error("Migration file for entity '{$entity}' not found in database/migrations.");

            return 1;
        }

        $this->info("Using migration: {$migrationPath}");

        try {
            $migrationParser = new MigrationParser;
            $parsed = $migrationParser->parse($migrationPath);
            $fields = $parsed['fields'] ?? [];

            $templateDataGenerator = new TemplateDataGenerator($fields, $module, $entity);
            $replacements = $templateDataGenerator->generate();

            $fileGenerator = new FileGenerator($this->output, $fields);
            $fileGenerator->generate($module, $entity, $replacements);

            $this->info("Generated modular structure with CRUD operations for {$entity} in module {$module}.");

            /*
            |--------------------------------------------------------------------------
            | Cross-platform Pint execution
            |--------------------------------------------------------------------------
            */
            $modulePath = base_path("app/Modules/{$module}");

            $this->info('Running Pint for formatting...');

            $pintCmd = $this->getPintCommand();

            exec("{$pintCmd} {$modulePath}", $output1);
            exec("{$pintCmd} bootstrap", $output2);

            foreach (array_merge($output1, $output2) as $line) {
                $this->line($line);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Error generating structure for {$entity}: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * Detect the correct executable format for Pint (Windows vs Linux/macOS).
     */
    private function getPintCommand(): string
    {
        // Windows uses "vendor\bin\pint.bat"
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return base_path('vendor\\bin\\pint.bat');
        }

        // MacOS/Linux use "./vendor/bin/pint"
        return base_path('vendor/bin/pint');
    }

    private function findMigrationPath(string $entity): ?string
    {
        $migrationDir = base_path('database/migrations');
        $entitySnake = \Illuminate\Support\Str::snake($entity);
        $entityPluralSnake = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::plural($entity));

        foreach (scandir($migrationDir) as $file) {
            if (
                str_contains($file, "create_{$entitySnake}_table") ||
                str_contains($file, "create_{$entityPluralSnake}_table")
            ) {
                return $migrationDir . DIRECTORY_SEPARATOR . $file;
            }
        }

        return null;
    }
}
