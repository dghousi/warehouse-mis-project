<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Frontend\DtoParser;
use App\Console\Commands\Frontend\FileChecker;
use App\Console\Commands\Frontend\FileGenerator;
use App\Console\Commands\Frontend\Logger;
use App\Console\Commands\Frontend\RequestParser;
use Illuminate\Console\Command;

final class GenerateFrontendStructureCommand extends Command
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    protected $signature = 'generate:frontend {module} {entity}';

    protected $description = 'Generate frontend configuration, localization, type definitions, and other files for a given module and entity';

    private const EXIT_CODES = [
        'SUCCESS' => 0,
        'DTO_NOT_FOUND' => 1,
        'CONFIG_STUB_NOT_FOUND' => 2,
        'LOCALE_STUB_NOT_FOUND' => 3,
        'INVALID_JSON' => 4,
        'PARSING_FAILED' => 5,
        'REQUEST_NOT_FOUND' => 6,
        'CREATE_USE_CASE_STUB_NOT_FOUND' => 7,
        'DELETE_USE_CASE_STUB_NOT_FOUND' => 8,
        'GET_USE_CASE_STUB_NOT_FOUND' => 9,
        'LIST_USE_CASE_STUB_NOT_FOUND' => 10,
        'UPDATE_USE_CASE_STUB_NOT_FOUND' => 11,
        'INTERFACE_STUB_NOT_FOUND' => 12,
        'REPOSITORY_STUB_NOT_FOUND' => 13,
        'STORE_STUB_NOT_FOUND' => 14,
        'USE_CREATE_STUB_NOT_FOUND' => 15,
        'USE_DELETE_STUB_NOT_FOUND' => 16,
        'USE_GET_STUB_NOT_FOUND' => 17,
        'USE_LIST_STUB_NOT_FOUND' => 18,
        'USE_UPDATE_STUB_NOT_FOUND' => 19,
        'INDEX_STUB_NOT_FOUND' => 20,
        'PAGE_STUB_NOT_FOUND' => 21,
        'TYPE_STUB_NOT_FOUND' => 22,
    ];

    public function handle(
        FileChecker $fileChecker,
        DtoParser $dtoParser,
        RequestParser $requestParser,
        FileGenerator $fileGenerator,
        Logger $logger
    ): int {
        $module = $this->argument('module');
        $entity = $this->argument('entity');
        $dtoPath = base_path("app/Modules/{$module}/Application/DTOs/{$entity}Data.php");

        $exitCode = self::EXIT_CODES['SUCCESS'];

        // Validate DTO file
        if (!$fileChecker->check($dtoPath, "DTO for {$entity} not found at {$dtoPath}.", $this, $logger)) {
            return self::EXIT_CODES['DTO_NOT_FOUND'];
        }

        // Validate stub files
        foreach (FileGenerator::STUB_PATHS as $type => $config) {
            $stubPath = base_path($config['stub']);
            if (!$fileChecker->check($stubPath, "Stub file for {$type} not found at {$stubPath}.", $this, $logger)) {
                return self::EXIT_CODES[strtoupper($type).'_STUB_NOT_FOUND'];
            }
        }

        try {
            $fields = $dtoParser->parse($dtoPath, $module, $entity);
            $requestFields = $requestParser->parse($module, $entity, $this, $logger);
            $fields = $this->mergeFields($fields, $requestFields);

            $fileGenerator->generate($module, $entity, $fields, $this, $logger);

            $this->info("Frontend files for {$module}/{$entity} generated successfully!");
        } catch (\Exception $e) {
            $logger->error("Failed to generate files: {$e->getMessage()}", $this);
            $exitCode = self::EXIT_CODES['PARSING_FAILED'];
        }

        return $exitCode;
    }

    private function mergeFields(array $fields, array $requestFields): array
    {
        foreach ($fields as &$field) {
            if (isset($requestFields[$field['name']])) {
                if (isset($requestFields[$field['name']]['enum'])) {
                    $field['options'] = $requestFields[$field['name']]['enum'];
                    $field['type'] = 'select';
                }
                $field['required'] = $requestFields[$field['name']]['required'] ?? false;
            } else {
                $field['required'] = false;
            }
        }

        return $fields;
    }
}
