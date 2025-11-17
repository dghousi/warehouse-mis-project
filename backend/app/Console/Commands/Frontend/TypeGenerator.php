<?php

declare(strict_types=1);

namespace App\Console\Commands\Frontend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class TypeGenerator
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    public function generate(string $module, string $entity, array $fields, Command $command, Logger $logger): void
    {
        $moduleDir = Str::kebab($module);
        $camelEntity = ucfirst($entity);

        $typeContent = $this->generateTypeContent($entity, $fields);
        $logger->info("Generated TypeScript type content:\n{$typeContent}", $command);

        $typePath = base_path("../frontend/src/modules/{$moduleDir}/domain/entities/{$camelEntity}.ts");
        File::ensureDirectoryExists(dirname($typePath));
        File::put($typePath, $typeContent);

        $logger->info("Generated TypeScript type: {$typePath}", $command);
    }

    public function generateTypeContent(string $entity, array $fields): string
    {
        // TypeScript-specific type mappings
        $typeMappings = config('frontend_generator.typescript_mappings', [
            'string' => 'string',
            'int' => 'number',
            'integer' => 'number',
            'float' => 'number',
            'double' => 'number',
            'bool' => 'boolean',
            'boolean' => 'boolean',
            'array' => 'any[]',
            'datetime' => 'string',
            'datetimeimmutable' => 'string',
            'date' => 'string',
            'enum' => 'string',
            'default' => 'string',
        ]);

        $specialFields = [
            'roles' => 'string[]',
            'permissions' => 'string[]',
            'attachment' => 'string',
            'attachmentUrl' => 'string',
        ];

        $fieldDefinitions = [];

        $fieldDefinitions[] = '  id: number;';

        foreach ($fields as $field) {
            $name = $field['name'];
            $phpType = $field['type'];
            $isNullable = $this->isNullable($phpType);

            // Use special mapping if field matches
            if (isset($specialFields[$name])) {
                $tsType = $specialFields[$name];
            } else {
                // Map PHP type to TypeScript type
                $cleanPhpType = strtolower((string) preg_replace('/^(\??)(\\?)?(\w+)/', '$3', (string) $phpType));
                $tsType = $typeMappings[$cleanPhpType] ?? $typeMappings['default'];
            }

            $optional = $isNullable ? '?' : '';
            $line = "  {$name}{$optional}: {$tsType};";

            $fieldDefinitions[] = $line;
        }

        $fieldDefinitions[] = '  createdAt: string;';
        $fieldDefinitions[] = '  updatedAt: string;';

        $fieldsString = implode("\n", $fieldDefinitions);

        return "export type {$entity} = {\n{$fieldsString}\n};";
    }

    private function isNullable(string $phpType): bool
    {
        return str_starts_with($phpType, '?') || str_contains($phpType, '|null');
    }
}
