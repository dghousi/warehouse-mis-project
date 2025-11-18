<?php

declare(strict_types=1);

namespace App\Console\Commands\Frontend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class FileGenerator
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    private const PLACEHOLDER_ENTITY = '{entity}';

    private const PLACEHOLDER_ENTITY_CAMEL = '{entityCamel}';

    public const STUB_PATHS = [
        'config' => [
            'stub' => 'stubs/frontend/config.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/presentation/config/{entityKebab}-config.ts',
            'naming' => 'kebab',
        ],
        'locale' => [
            'stub' => 'stubs/frontend/locale.stub',
            'output' => '../frontend/src/messages/{locale}/{moduleKebab}/{entityKebab}.json',
            'naming' => 'kebab',
        ],
        'type' => [
            'stub' => 'stubs/frontend/type.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/domain/entities/{entity}.ts',
            'naming' => 'pascal',
        ],
        'create-use-case' => [
            'stub' => 'stubs/frontend/create-use-case.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/application/use-cases/{entityKebabPlural}/create-{entityKebab}-use-case.ts',
            'naming' => 'kebab',
        ],
        'delete-use-case' => [
            'stub' => 'stubs/frontend/delete-use-case.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/application/use-cases/{entityKebabPlural}/delete-{entityKebab}-use-case.ts',
            'naming' => 'kebab',
        ],
        'get-use-case' => [
            'stub' => 'stubs/frontend/get-use-case.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/application/use-cases/{entityKebabPlural}/get-{entityKebab}-use-case.ts',
            'naming' => 'kebab',
        ],
        'list-use-case' => [
            'stub' => 'stubs/frontend/list-use-case.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/application/use-cases/{entityKebabPlural}/list-{entityKebabPlural}-use-case.ts',
            'naming' => 'kebab',
        ],
        'update-use-case' => [
            'stub' => 'stubs/frontend/update-use-case.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/application/use-cases/{entityKebabPlural}/update-{entityKebab}-use-case.ts',
            'naming' => 'kebab',
        ],
        'interface' => [
            'stub' => 'stubs/frontend/interface.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/domain/interfaces/{entity}RepositoryInterface.ts',
            'naming' => 'pascal',
        ],
        'repository' => [
            'stub' => 'stubs/frontend/repository.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/infrastructure/repositories/{entityKebab}-repository.ts',
            'naming' => 'kebab',
        ],
        'store' => [
            'stub' => 'stubs/frontend/store.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/application/stores/{entityKebab}-store.ts',
            'naming' => 'kebab',
        ],
        'use-create' => [
            'stub' => 'stubs/frontend/use-create.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/presentation/hooks/{entityKebabPlural}/useCreate{entity}.ts',
            'naming' => 'hook',
        ],
        'use-delete' => [
            'stub' => 'stubs/frontend/use-delete.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/presentation/hooks/{entityKebabPlural}/useDelete{entity}.ts',
            'naming' => 'hook',
        ],
        'use-get' => [
            'stub' => 'stubs/frontend/use-get.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/presentation/hooks/{entityKebabPlural}/useGet{entity}.ts',
            'naming' => 'hook',
        ],
        'use-list' => [
            'stub' => 'stubs/frontend/use-list.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/presentation/hooks/{entityKebabPlural}/use{entity}List.ts',
            'naming' => 'hook',
        ],
        'use-update' => [
            'stub' => 'stubs/frontend/use-update.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/presentation/hooks/{entityKebabPlural}/useUpdate{entity}.ts',
            'naming' => 'hook',
        ],
        'index' => [
            'stub' => 'stubs/frontend/index.stub',
            'output' => '../frontend/src/modules/{moduleKebab}/presentation/hooks/index.ts',
            'naming' => 'index',
        ],
        'page' => [
            'stub' => 'stubs/frontend/page.stub',
            'output' => '../frontend/src/app/[locale]/{moduleKebab}/{entityKebabPlural}/page.tsx',
            'naming' => 'kebab',
        ],
        'request' => [
            'stub' => 'stubs/frontend/request.stub',
            'output' => '../frontend/src/i18n/request.ts',
            'naming' => 'request',
        ],
    ];

    public function generate(
        string $module,
        string $entity,
        array $fields,
        Command $command,
        Logger $logger,
        array $replacements = []
    ): void {
        $replacements = $this->buildReplacements($module, $entity, $replacements);

        foreach (self::STUB_PATHS as $type => $config) {
            $this->processStub($type, $config, $replacements, $fields, $command, $logger, $module, $entity);
        }
    }

    private function buildReplacements(string $module, string $entity, array $replacements): array
    {
        $moduleKebab = Str::kebab($module);
        $entityKebab = Str::kebab($entity);
        $entityCamel = lcfirst($entity);
        $entityPascal = ucfirst($entity);
        $entityKebabPlural = Str::plural($entityKebab);
        $entityCamelPlural = Str::plural($entityCamel);
        $entitySnakeUpper = Str::upper(Str::snake($entity, '_'));
        $moduleCamel = lcfirst($module);
        $entityPlural = Str::plural($entityPascal);
        $entityHeadLinePlural = Str::plural(Str::headline($entity));
        $entityHeadLine = Str::headline($entity);

        return array_merge([
            '{moduleKebab}' => $moduleKebab,
            '{entityKebab}' => $entityKebab,
            self::PLACEHOLDER_ENTITY => $entityPascal,
            self::PLACEHOLDER_ENTITY_CAMEL => $entityCamel,
            '{entityKebabPlural}' => $entityKebabPlural,
            '{entityCamelPlural}' => $entityCamelPlural,
            '{entitySnakeUpper}' => $entitySnakeUpper,
            '{moduleCamel}' => $moduleCamel,
            '{entityName}' => $entityCamel,
            '{queryKey}' => $entityCamelPlural,
            '{singleQueryKey}' => $entityCamel,
            '{entityPlural}' => $entityPlural,
            '{entityHeadLinePlural}' => $entityHeadLinePlural,
            '{entityHeadLine}' => $entityHeadLine,
        ], $replacements);
    }

    private function processStub(string $type, array $config, array $replacements, array $fields, Command $command, Logger $logger, string $module = '', string $entity = ''): void // NOSONAR
    {
        $stubPath = base_path($config['stub']);
        $outputPathTemplate = $config['output'];

        $logger->info("Reading stub: {$stubPath}", $command);
        if (!File::exists($stubPath)) {
            $logger->error("Stub file not found at {$stubPath}.", $command);
            throw new \Exception("Stub file not found: {$stubPath}"); // NOSONAR
        }

        $content = File::get($stubPath);

        if ($type === 'locale') {
            $this->generateLocaleFiles($config, $replacements, $fields, $command, $logger);
        } elseif ($type === 'index') {
            $this->generateIndexFile($config, $replacements, $command, $logger);
        } elseif ($type === 'request') {
            $this->generateRequestFile($config, $replacements, $fields, $command, $logger);
        } else {
            $outputPath = base_path(str_replace(
                array_keys($replacements),
                array_values($replacements),
                $outputPathTemplate
            ));
            $content = $this->replacePlaceholders($content, $replacements, $fields, $type, $module, $entity);
            $this->writeFile($outputPath, $content, $logger, $command);
        }
    }

    private function generateLocaleFiles(
        array $config,
        array $replacements,
        array $fields,
        Command $command,
        Logger $logger
    ): void {
        foreach (LocaleGenerator::SUPPORTED_LOCALES as $locale) {
            $localeReplacements = array_merge($replacements, ['{locale}' => $locale]);
            $outputPath = base_path(str_replace(
                array_keys($localeReplacements),
                array_values($localeReplacements),
                $config['output']
            ));

            $content = File::get(base_path($config['stub']));
            $content = $this->replacePlaceholders($content, $localeReplacements, $fields, 'locale');
            $this->writeFile($outputPath, $content, $logger, $command);
        }
    }

    private function generateIndexFile(
        array $config,
        array $replacements,
        Command $command,
        Logger $logger
    ): void {
        $outputPath = base_path(str_replace(
            array_keys($replacements),
            array_values($replacements),
            $config['output']
        ));

        $entityPascal = $replacements[self::PLACEHOLDER_ENTITY];
        $entityKebabPlural = $replacements['{entityKebabPlural}'];
        $exportStatements = [
            "export { useCreate{$entityPascal} } from './{$entityKebabPlural}/useCreate{$entityPascal}';",
            "export { useDelete{$entityPascal} } from './{$entityKebabPlural}/useDelete{$entityPascal}';",
            "export { useGet{$entityPascal} } from './{$entityKebabPlural}/useGet{$entityPascal}';",
            "export { use{$entityPascal}List } from './{$entityKebabPlural}/use{$entityPascal}List';",
            "export { useUpdate{$entityPascal} } from './{$entityKebabPlural}/useUpdate{$entityPascal}';",
        ];

        if (File::exists($outputPath)) {
            $existingContent = File::get($outputPath);
            if (!str_contains($existingContent, "useCreate{$entityPascal}")) {
                $newContent = rtrim($existingContent)."\n\n".implode("\n", $exportStatements);
                $this->writeFile($outputPath, $newContent, $logger, $command);
            }
        } else {
            $content = "/* eslint-disable */  // NOSONAR\n".implode("\n", $exportStatements);
            $this->writeFile($outputPath, $content, $logger, $command);
        }
    }

    private function generateRequestFile(
        array $config,
        array $replacements,
        array $fields,
        Command $command,
        Logger $logger
    ): void {
        $outputPath = base_path($config['output']);
        $entityCamel = $replacements[self::PLACEHOLDER_ENTITY_CAMEL];
        $moduleKebab = $replacements['{moduleKebab}'];
        $entityKebab = $replacements['{entityKebab}'];

        $typeEntry = "  {$entityCamel}: Record<string, unknown>;";
        $messageEntry = "    {$entityCamel}: (await import(`@/messages/\${locale}/{$moduleKebab}/{$entityKebab}.json`)).default,";

        if (File::exists($outputPath)) {
            $existingContent = File::get($outputPath);
            if (!str_contains($existingContent, "{$entityCamel}: Record<string, unknown>")) {
                $newContent = $this->updateRequestContent($existingContent, $typeEntry, $messageEntry, $logger, $command);
                $this->writeFile($outputPath, $newContent, $logger, $command);
            } else {
                $logger->info("Skipping request.ts update: {$entityCamel} already exists.", $command);
            }
        } else {
            $content = File::get(base_path($config['stub']));
            $content = $this->replacePlaceholders($content, $replacements, $fields, 'request');
            $this->writeFile($outputPath, $content, $logger, $command);
        }
    }

    private function updateRequestContent(
        string $existingContent,
        string $typeEntry,
        string $messageEntry,
        Logger $logger,
        Command $command
    ): string {
        $newContent = $existingContent;

        // Update Messages type
        $typePattern = '/(type Messages\s*=\s*\{)([^}]*?)(\s*\})/s';
        if (preg_match($typePattern, $newContent, $matches)) {
            $existingType = trim($matches[2]);
            $newInterface = $existingType !== '' && $existingType !== '0' ? "$existingType\n$typeEntry" : $typeEntry;
            $newContent = preg_replace(
                $typePattern,
                "$1\n$newInterface\n$3",
                $newContent
            );
        } else {
            $logger->error('Failed to update Messages type in request.ts: Invalid format.', $command);

            return $newContent;
        }

        // Update messages object
        $messagesPattern = '/(const messages: Messages\s*=\s*\{)(.*?)(\s*\}\s*;)/s';
        if (preg_match($messagesPattern, (string) $newContent, $matches)) {
            $existingMessages = trim($matches[2]);
            $existingMessages = rtrim($existingMessages, ",\n");
            $newMessages = $existingMessages !== '' && $existingMessages !== '0' ? "$existingMessages,\n$messageEntry" : $messageEntry;
            $newContent = preg_replace(
                $messagesPattern,
                "$1\n$newMessages\n$3",
                (string) $newContent
            );
        } else {
            $logger->error('Failed to update messages object in request.ts: Invalid format.', $command);
        }

        return $newContent;
    }

    private function replacePlaceholders(string $content, array $replacements, array $fields, string $type, string $module = '', string $entity = ''): string
    {
        if ($type === 'config') {
            $configGenerator = new ConfigGenerator;
            $columns = $configGenerator->generateColumns($fields);
            $formFields = $configGenerator->generateFormFields($fields);
            $filters = $configGenerator->generateFilters($module, $entity);
            $searchFields = $configGenerator->generateSearchFields($module, $entity);
            $sortableColumns = $configGenerator->generateSortableColumns($module, $entity);
            $relations = $configGenerator->generateRelations($module, $entity);

            $additionalReplacements = [
                '{columns}' => implode(ConfigGenerator::INDENT_SEPARATOR, $columns),
                '{formFields}' => implode(ConfigGenerator::INDENT_SEPARATOR, $formFields),
                '{filters}' => $filters !== [] ? implode(ConfigGenerator::INDENT_SEPARATOR, $filters) : '',
                '{searchFields}' => $searchFields,
                '{sortableColumns}' => $sortableColumns,
                '{relations}' => $relations,
                '{title}' => "config.{$replacements[self::PLACEHOLDER_ENTITY_CAMEL]}",
                '{createTitle}' => 'config.table.create',
                '{editTitle}' => 'config.table.edit',
                '{createButton}' => 'config.table.add',
                '{editButton}' => 'config.table.update',
                '{createSubmitText}' => 'config.create',
                '{editSubmitText}' => 'config.update',
            ];
            $replacements = array_merge($replacements, $additionalReplacements);
        } elseif ($type === 'locale') {
            $fieldEntries = array_map(fn (array $f): string => (new LocaleGenerator)->generateLocaleField($f), $fields);
            $fieldsJson = $fieldEntries !== [] ? implode(LocaleGenerator::INDENT_SEPARATOR, $fieldEntries) : '';
            $tableJson = (new LocaleGenerator)->generateTableTranslations($replacements[self::PLACEHOLDER_ENTITY]);
            $repoJson = (new LocaleGenerator)->generateRepositoryTranslations($replacements[self::PLACEHOLDER_ENTITY]);
            $presentationJson = (new LocaleGenerator)->generatePresentationTranslations($replacements[self::PLACEHOLDER_ENTITY]);

            $additionalReplacements = [
                '{fields}' => $fieldsJson,
                '{table}' => $tableJson,
                '{repository}' => $repoJson,
                '{presentation}' => $presentationJson,
            ];
            $replacements = array_merge($replacements, $additionalReplacements);
        } elseif ($type === 'type') {
            $content = (new TypeGenerator)->generateTypeContent($replacements[self::PLACEHOLDER_ENTITY], $fields);
        }

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private function writeFile(string $path, string $content, Logger $logger, Command $command): void
    {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $logger->info("Generated file: {$path}", $command);
    }
}
