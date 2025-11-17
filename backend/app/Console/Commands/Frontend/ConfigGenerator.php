<?php

declare(strict_types=1);

namespace App\Console\Commands\Frontend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final readonly class ConfigGenerator
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    private const EXCLUDED_FIELDS = ['createdAt', 'updatedAt', 'deletedAt', 'deletedBy', 'rememberToken'];

    public const INDENT_SEPARATOR = ",\n  ";

    private ListRequestParser $listRequestParser;

    public function __construct()
    {
        $this->listRequestParser = new ListRequestParser;
    }

    public function generate(string $module, string $entity, array $fields, Command $command, Logger $logger): void
    {
        $camelEntity = lcfirst($entity);
        $kebabEntity = Str::kebab($entity);
        $entityPlural = Str::plural($camelEntity);
        $moduleDir = Str::kebab($module);

        $stubPath = base_path('stubs/frontend/config.stub');
        $logger->info("Reading config stub: {$stubPath}", $command);
        $content = File::get($stubPath);

        $columns = $this->generateColumns($fields);
        $formFields = $this->generateFormFields($fields);
        $filters = $this->generateFilters($module, $entity);
        $searchFields = $this->generateSearchFields($module, $entity);
        $sortableColumns = $this->generateSortableColumns($module, $entity);
        $relations = $this->generateRelations($module, $entity);
        $searchField = $this->getSearchField($fields);

        $replacements = [
            '{entityName}' => $camelEntity,
            '{columns}' => implode(self::INDENT_SEPARATOR, $columns),
            '{formFields}' => implode(self::INDENT_SEPARATOR, $formFields),
            '{filters}' => implode(self::INDENT_SEPARATOR, $filters),
            '{searchFields}' => $searchFields,
            '{sortableColumns}' => $sortableColumns,
            '{relations}' => $relations,
            '{queryKey}' => $entityPlural,
            '{singleQueryKey}' => $camelEntity,
            '{searchField}' => $searchField,
            '{title}' => "config.{$camelEntity}",
            '{createTitle}' => 'config.table.create',
            '{editTitle}' => 'config.table.edit',
            '{createButton}' => 'config.table.add',
            '{editButton}' => 'config.table.update',
            '{createSubmitText}' => 'config.create',
            '{editSubmitText}' => 'config.update',
        ];

        $configContent = str_replace(array_keys($replacements), array_values($replacements), $content);

        $lines = explode("\n", $configContent);
        $formattedLines = [];
        $indentLevel = 0;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            if (preg_match('/^[}\]]/', $trimmed)) {
                $indentLevel--;
            }
            $formattedLines[] = str_repeat('  ', max(0, $indentLevel)).$trimmed;
            if (preg_match('/[{[]$/', $trimmed)) {
                $indentLevel++;
            }
        }
        $configContent = implode("\n", $formattedLines);

        $logger->info("Generated config content:\n".$configContent, $command);

        $configPath = base_path("../frontend/src/modules/{$moduleDir}/presentation/config/{$kebabEntity}-config.ts");
        File::ensureDirectoryExists(dirname($configPath));
        File::put($configPath, $configContent);

        $logger->info("Generated config: {$configPath}", $command);
    }

    public function generateColumns(array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            if (in_array($field['name'], self::EXCLUDED_FIELDS, true)) {
                continue;
            }
            $out[] = sprintf(
                "{\n    accessorKey: '%s',\n    header: 'config.form.%s.label'\n  }",
                $field['name'],
                $field['name']
            );
        }

        return $out;
    }

    public function generateFormFields(array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            if (in_array($field['name'], self::EXCLUDED_FIELDS, true)) {
                continue;
            }

            $validations = $this->getValidations($field);

            $type = match ($field['name']) {
                'email' => 'email',
                'password' => 'password',
                'remarks' => 'textarea',
                'attachment' => 'file',
                default => $field['type'],
            };

            $validationBlock = '';
            if ($validations !== []) {
                $validationItems = array_map(
                    fn ($v): string|array => str_replace(['{name}', "\n        "], [$field['name'], ''], $v),
                    $validations
                );
                $validationBlock = ",\n    validation: {\n      ".implode(",\n      ", $validationItems)."\n    }"; // NOSONAR
            }

            $optionsBlock = '';
            if (!empty($field['options'])) {
                $optionsArray = array_map(
                    fn ($opt): string => sprintf(
                        "{\n        label: \"config.form.%s.options.%s\",\n        value: \"%s\"\n      }",
                        $field['name'],
                        $opt,
                        $opt
                    ),
                    $field['options']
                );
                $optionsJson = "[\n      ".implode(",\n      ", $optionsArray)."\n    ]"; // NOSONAR
                $validationBlock = str_replace('{options}', json_encode($field['options']), $validationBlock);
                $optionsBlock = ",\n    options: $optionsJson";
            }

            $out[] = sprintf(
                "{\n    label: 'config.form.%s.label',\n    name: '%s',\n    required: %s,\n    type: '%s'%s%s\n  }",
                $field['name'],
                $field['name'],
                $field['required'] ? 'true' : 'false',
                $type,
                $validationBlock,
                $optionsBlock
            );
        }

        return $out;
    }

    private function getValidations(array $field): array
    {
        $rules = [];
        $validations = config('frontend_generator.validations', [
            'required' => [
                'condition' => fn ($f) => $f['required'],
                'rule' => 'required: "config.form.{name}.validation.required"',
            ],
            'email' => [
                'condition' => fn ($f): bool => $f['name'] === 'email',
                'rule' => 'pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: "config.form.{name}.validation.email" }',
            ],
            'password' => [
                'condition' => fn ($f): bool => $f['name'] === 'password',
                'rule' => 'minLength: { value: 8, message: "config.form.{name}.validation.minLength" }',
            ],
            'max_length' => [
                'condition' => fn ($f): bool => in_array($f['type'], ['text', 'textarea', 'password', 'email']) || in_array($f['name'], ['roles', 'permissions']),
                'rule' => 'maxLength: { value: 255, message: "config.form.{name}.validation.maxLength" }',
            ],
            'id_field' => [
                'condition' => fn ($f): bool => in_array($f['name'], ['reportToId', 'mainOrganizationId', 'createdBy', 'updatedBy']),
                'rule' => 'pattern: { value: /^\d+$/, message: "config.form.{name}.validation.pattern" }',
            ],
            'file' => [
                'condition' => fn ($f): bool => $f['name'] === 'attachment',
                'rule' => 'mimes: { value: ["jpg", "jpeg", "png"], message: "config.form.{name}.validation.mimes" }',
            ],
            'file_size' => [
                'condition' => fn ($f): bool => $f['name'] === 'attachment',
                'rule' => 'maxSize: { value: 5120, message: "config.form.{name}.validation.maxSize" }',
            ],
            'enum' => [
                'condition' => fn ($f): bool => !empty($f['options']),
                'rule' => 'in: { value: {options}, message: "config.form.{name}.validation.in" }',
            ],
        ]);

        foreach ($validations as $validation) {
            if ($validation['condition']($field)) {
                $rules[] = $validation['rule'];
            }
        }

        return $rules;
    }

    public function generateFilters(string $module, string $entity): array
    {
        $data = $this->listRequestParser->parse($module, $entity);
        $filters = $data['filters'];

        $out = [];
        foreach ($filters as $key => $options) {
            $optionsTs = array_map(
                fn ($opt): string => "{ label: 'config.form.{$key}.options.{$opt}', value: '{$opt}' }",
                $options
            );
            $out[] = sprintf(
                "{\n    key: '%s',\n    title: 'config.form.%s.label',\n    options: [\n      %s\n    ]\n  }",
                $key,
                $key,
                implode(",\n      ", $optionsTs)
            );
        }

        return $out;
    }

    public function generateSearchFields(string $module, string $entity): string
    {
        $listRequestParser = new ListRequestParser;
        $searchableKeys = $listRequestParser->parse($module, $entity)['searchable'];

        if (empty($searchableKeys)) {
            return '[]';
        }

        $items = array_map(
            fn ($key): string => "{ label: 'config.form.{$key}.label', value: '{$key}' }",
            $searchableKeys
        );

        return "[\n    ".implode(",\n    ", $items)."\n  ]";
    }

    public function getSearchField(array $fields): string
    {
        $searchable = [];
        foreach ($fields as $field) {
            if (in_array($field['name'], ['firstName', 'lastName', 'email']) && !in_array($field['name'], self::EXCLUDED_FIELDS, true)) {
                $searchable[] = $field['name'];
            }
        }

        return $searchable[0] ?? '';
    }

    public function generateSortableColumns(string $module, string $entity): string
    {
        $data = $this->listRequestParser->parse($module, $entity);
        $sortable = $data['sortable'];

        if (empty($sortable)) {
            return '[]';
        }

        $items = array_map(fn ($col): string => "'{$col}'", $sortable);

        return '['.implode(', ', $items).']';
    }

    public function generateRelations(string $module, string $entity): string
    {
        $data = $this->listRequestParser->parse($module, $entity);
        $relations = $data['relations'];

        if (empty($relations)) {
            return '[]';
        }

        $items = array_map(fn ($rel): string => "'{$rel}'", $relations);

        return '['.implode(', ', $items).']';
    }
}
