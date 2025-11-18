<?php

declare(strict_types=1);

namespace App\Console\Commands\Backend;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class FileGenerator
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    private const ENTITY = '{{ entity }}';

    public function __construct(private OutputInterface $output, private array $fields = []) {}

    public function generate(string $module, string $entity, array $replacements): void
    {
        $base = base_path("app/Modules/{$module}");
        $entityPlural = Str::plural($entity);

        $files = [
            'store-request.stub' => "{$base}/Infrastructure/Http/Requests/{$entity}/Store{$entity}Request.php",
            'update-request.stub' => "{$base}/Infrastructure/Http/Requests/{$entity}/Update{$entity}Request.php",
            'list-request.stub' => "{$base}/Infrastructure/Http/Requests/{$entity}/List{$entityPlural}Request.php",
            'bulk-delete-request.stub' => "{$base}/Infrastructure/Http/Requests/{$entity}/BulkDelete{$entityPlural}Request.php",
            'dto.stub' => "{$base}/Application/DTOs/{$entity}Data.php",
            'resource.stub' => "{$base}/Infrastructure/Resources/{$entity}Resource.php",
            'model.stub' => "{$base}/Domain/Entities/{$entity}.php",
            'create-usecase.stub' => "{$base}/Application/UseCases/{$entity}/Create{$entity}UseCase.php",
            'delete-usecase.stub' => "{$base}/Application/UseCases/{$entity}/Delete{$entity}UseCase.php",
            'get-usecase.stub' => "{$base}/Application/UseCases/{$entity}/Get{$entity}UseCase.php",
            'list-usecase.stub' => "{$base}/Application/UseCases/{$entity}/List{$entityPlural}UseCase.php",
            'update-usecase.stub' => "{$base}/Application/UseCases/{$entity}/Update{$entity}UseCase.php",
            'restore-usecase.stub' => "{$base}/Application/UseCases/{$entity}/Restore{$entity}UseCase.php",
            'force-delete-usecase.stub' => "{$base}/Application/UseCases/{$entity}/ForceDelete{$entity}UseCase.php",
            'bulk-delete-usecase.stub' => "{$base}/Application/UseCases/{$entity}/BulkDelete{$entityPlural}UseCase.php",
            'repository-interface.stub' => "{$base}/Domain/Repositories/{$entity}RepositoryInterface.php",
            'repository-eloquent.stub' => "{$base}/Infrastructure/Repositories/Eloquent{$entity}Repository.php",
            'service.stub' => "{$base}/Domain/Services/{$entity}Service.php",
            'controller.stub' => "{$base}/Infrastructure/Http/Controllers/{$entity}Controller.php",
        ];

        foreach ($files as $stub => $dest) {
            $template = File::get(base_path("stubs/backend/{$stub}"));
            $filled = $this->fillTemplate($template, $replacements);
            File::ensureDirectoryExists(dirname($dest));
            File::put($dest, $filled);
            $this->output->writeln("Created: {$dest}");
        }

        // Append service provider binding
        $this->appendServiceProvider($module, $entity, $replacements);

        // Append routes
        $this->appendRoutes($module, $entity, $replacements);

        $this->registerModuleProvider($module);

        $this->updateLocalizationFile($module, $entity);
    }

    private function appendServiceProvider(string $module, string $entity, array $replacements): void
    {
        $dest = base_path("app/Modules/{$module}/Infrastructure/Providers/{$module}ServiceProvider.php");
        $stub = File::get(base_path('stubs/backend/service-provider.stub'));
        $filled = $this->fillTemplate($stub, $replacements);

        $interfaceUse = "use App\\Modules\\{$module}\\Domain\\Repositories\\{$entity}RepositoryInterface;";
        $eloquentUse = "use App\\Modules\\{$module}\\Infrastructure\\Repositories\\Eloquent{$entity}Repository;";

        if (!File::exists($dest)) {
            File::ensureDirectoryExists(dirname($dest));
            File::put($dest, $filled);
            $this->output->writeln("Created: {$dest}");

            return;
        }

        $existingContent = File::get($dest);

        // Prevent duplicate bindings
        $binding = "\$this->app->bind(abstract: {$entity}RepositoryInterface::class, concrete: Eloquent{$entity}Repository::class);";
        if (Str::contains($existingContent, $binding)) {
            $this->output->writeln("Binding for {$entity} already exists in {$dest}, skipping.");

            return;
        }

        // Inject use statements after namespace
        $lines = explode("\n", $existingContent);
        $namespaceIndex = collect($lines)->search(fn ($line) => Str::startsWith(trim($line), 'namespace '));
        $insertIndex = $namespaceIndex !== false ? $namespaceIndex + 1 : 1;

        $existingUses = collect($lines)->filter(fn ($line) => Str::startsWith(trim($line), 'use'))->map('trim')->all();

        if (!in_array(trim($interfaceUse), $existingUses)) {
            array_splice($lines, $insertIndex, 0, $interfaceUse);
            $insertIndex++;
        }

        if (!in_array(trim($eloquentUse), $existingUses)) {
            array_splice($lines, $insertIndex, 0, $eloquentUse);
        }

        // Inject binding into registerBindings()
        $newBinding = $this->fillTemplate(
            '        $this->app->bind(abstract: {{ entity }}RepositoryInterface::class, concrete: Eloquent{{ entity }}Repository::class);',
            $replacements
        );

        $lines = preg_replace_callback(
            '/(protected function registerBindings\(\): void\s*{)/',
            fn ($match): string => $match[1]."\n".$newBinding,
            implode("\n", $lines)
        );

        File::put($dest, $lines);
        $this->output->writeln("Appended binding to: {$dest}");
    }

    private function appendRoutes(string $module, string $entity, array $replacements): void
    {
        $dest = base_path("app/Modules/{$module}/Routes/api.php");
        $stub = File::get(base_path('stubs/backend/api-routes.stub'));
        $this->fillTemplate($stub, $replacements);

        $moduleSnake = Str::snake($module);

        $entityKebabPlural = $replacements['{{ entityKebabPlural }}'];
        $controllerClass = "App\\Modules\\{$module}\\Infrastructure\\Http\\Controllers\\{$entity}Controller";

        if (!File::exists($dest)) {
            File::ensureDirectoryExists(dirname($dest));
            File::put($dest, "<?php\n\nuse {$controllerClass};\nuse Illuminate\Support\Facades\Route;\n\nRoute::middleware(['api', 'auth:sanctum', 'set_locale'])->prefix('api/v1/{$moduleSnake}')->group(function (): void {
            Route::post('/{$entityKebabPlural}/restore/{id}', [{$entity}Controller::class, 'restore']);
            Route::delete('/{$entityKebabPlural}/force/{id}', [{$entity}Controller::class, 'forceDelete']);
            Route::delete('/{$entityKebabPlural}/bulk', [{$entity}Controller::class, 'bulkDelete']);
            Route::apiResource('{$entityKebabPlural}', {$entity}Controller::class);
            });");
            $this->output->writeln("Created: {$dest}");

            return;
        }

        $existingContent = File::get($dest);

        if (Str::contains($existingContent, "Route::apiResource('{$entityKebabPlural}',")) {
            $this->output->writeln("Route for {$entityKebabPlural} already exists in {$dest}, skipping.");

            return;
        }

        $useStatements = [];
        $routeLines = [];
        $inGroup = false;
        $lines = explode("\n", $existingContent);

        foreach ($lines as $line) {
            if (preg_match('/^use\s+[^;]+;/', $line)) {
                $useStatements[] = trim($line);
            } elseif (Str::contains($line, "Route::middleware(['api', 'auth:sanctum', 'set_locale'])->prefix('api/v1/{$moduleSnake}')->group(function (): void {")) {
                $inGroup = true;
            } elseif ($inGroup && Str::contains($line, '});')) {
                $inGroup = false;
            } elseif ($inGroup && trim($line) !== '') {
                $routeLines[] = trim($line);
            }
        }

        // Add new use statements
        $useStatements[] = "use {$controllerClass};";
        $useStatements[] = 'use Illuminate\Support\Facades\Route;';
        $useStatements = array_unique($useStatements);
        sort($useStatements);

        // Add new route
        $routeLines[] = "
        Route::post('/{$entityKebabPlural}/restore/{id}', [{$entity}Controller::class, 'restore']);
        Route::delete('/{$entityKebabPlural}/force/{id}', [{$entity}Controller::class, 'forceDelete']);
        Route::delete('/{$entityKebabPlural}/bulk', [{$entity}Controller::class, 'bulkDelete']);
        Route::apiResource('{$entityKebabPlural}', {$entity}Controller::class);";
        $routeLines = array_unique($routeLines);

        // Rebuild file
        $newContent = "<?php\n\n";
        $newContent .= implode("\n", $useStatements)."\n\n";
        $newContent .= "Route::middleware(['api', 'auth:sanctum', 'set_locale'])->prefix('api/v1/{$moduleSnake}')->group(function (): void {\n";
        $newContent .= '    '.implode("\n    ", $routeLines)."\n";
        $newContent .= '});';

        File::put($dest, $newContent);
        $this->output->writeln("Appended route to: {$dest}");
    }

    private function fillTemplate(string $template, array $replacements): string
    {
        $hasManyRelationships = collect($this->fields)
            ->filter(fn ($f): bool => isset($f['relationship']) && $f['relationship']['type'] === 'HasMany')
            ->pluck('relationship.name')
            ->map(fn ($name): string => "'{$name}'")
            ->implode(', ');

        $additional = [
            '{{ moduleSnake }}' => Str::snake($replacements['{{ module }}']),
            '{{ entitySnake }}' => Str::snake($replacements[self::ENTITY]),
            '{{ entityCamel }}' => lcfirst(string: (string) $replacements[self::ENTITY]),
            '{{ entitySnakePlural }}' => Str::plural(Str::snake($replacements[self::ENTITY])),
            '{{ entitySnakeUpper }}' => Str::upper(Str::snake($replacements[self::ENTITY])),
            '{{ entityKebabPlural }}' => Str::kebab(Str::plural($replacements[self::ENTITY])),
            '{{ relationships }}' => $replacements['{{ relationships }}'] ?? '',
            '{{ withHasMany }}' => $hasManyRelationships ? "with([$hasManyRelationships])->" : '',
        ];

        $template = str_replace(
            array_keys($additional),
            array_values($additional),
            $template
        );

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }

    protected function registerModuleProvider(string $module): void
    {
        $providerClass = "App\\Modules\\{$module}\\Infrastructure\\Providers\\{$module}ServiceProvider::class";
        $providerPath = base_path('bootstrap/providers.php');

        $contents = file_get_contents($providerPath);

        // Normalize line endings and search safely
        if (str_contains($contents, $providerClass)) {
            return;
        }

        // Insert before closing bracket ]
        $pattern = '/(return\s*\[\s*)(.*?)(\s*\];)/s';

        if (preg_match($pattern, $contents, $matches)) {
            $existing = trim($matches[2]);
            $newEntry = "    {$providerClass},";
            $newList = $existing !== '' && $existing !== '0' ? "{$existing}\n{$newEntry}" : $newEntry;
            $newContents = "return [\n{$newList}\n];";

            file_put_contents($providerPath, "<?php\n\n{$newContents}");
        }
    }

    protected function updateLocalizationFile(string $module, string $entity): void
    {
        $languages = ['en', 'dr', 'ps'];
        $translationKeys = $this->generateTranslationKeys($entity);

        foreach ($languages as $language) {
            $langPath = base_path("app/Modules/{$module}/Resources/lang/{$language}/messages.php");
            $this->createOrUpdateLangFile($langPath, $translationKeys);
        }
    }

    /**
     * Generate translation keys for the entity.
     */
    private function generateTranslationKeys(string $entity): array
    {
        $snake = Str::snake($entity);
        $plural = Str::snake(Str::plural($entity));
        $readable = Str::headline($entity);
        $pluralReadable = Str::headline(Str::plural($entity));

        return [
            "{$plural}.fetched" => "{$pluralReadable} fetched successfully.",
            "{$snake}.created" => "{$readable} created successfully.",
            "{$snake}.fetched" => "{$readable} fetched successfully.",
            "{$snake}.updated" => "{$readable} updated successfully.",
            "{$snake}.deleted" => "{$readable} deleted successfully.",
            "{$snake}.restored" => "{$readable} restored successfully.",
            "{$snake}.force_deleted" => "{$readable} force deleted successfully.",
            "{$plural}.bulk_deleted" => "{$pluralReadable} bulk deleted successfully.",
            "{$plural}.bulk_skipped" => "Some {$pluralReadable} were skipped due to related records.",
        ];
    }

    private function createOrUpdateLangFile(string $langPath, array $newKeys): void
    {
        if (!file_exists($langPath)) {
            $this->createLangFile($langPath, $newKeys);

            return;
        }

        $this->updateLangFile($langPath, $newKeys);
    }

    private function createLangFile(string $langPath, array $newKeys): void
    {
        $dir = dirname($langPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = $this->buildLangFileContent($newKeys);
        file_put_contents($langPath, $content);
        $this->output->writeln("Created localization file: {$langPath}");
    }

    private function updateLangFile(string $langPath, array $newKeys): void
    {
        $existing = include $langPath; // NOSONAR
        $merged = array_merge($existing, array_diff_key($newKeys, $existing));

        $content = $this->buildLangFileContent($merged);
        file_put_contents($langPath, $content);
        $this->output->writeln("Updated localization file: {$langPath}");
    }

    private function buildLangFileContent(array $keys): string
    {
        $lines = ['<?php', '', 'return ['];
        foreach ($keys as $key => $value) {
            $lines[] = "    '{$key}' => '{$value}',";
        }
        $lines[] = '];';

        return implode("\n", $lines);
    }
}
