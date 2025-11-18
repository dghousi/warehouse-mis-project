<?php

declare(strict_types=1);

namespace App\Console\Commands\Backend;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final readonly class TemplateDataGenerator // NOSONAR
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    private string $entitySnake;

    private array $constants;

    public function __construct(
        private array $fields,
        private string $module,
        private string $entity
    ) {
        $this->entitySnake = Str::snake($this->entity);
        $this->constants = $this->extractConstantsFromMigration();
    }

    public function generate(): array
    {
        $foreignKeyNames = $this->getForeignKeyNames();
        $hasManyRelationships = $this->getHasManyRelationships();
        $entityPlural = Str::plural($this->entity);
        $entitySnakePlural = Str::snake($entityPlural);
        $entityKebabPlural = Str::kebab($entityPlural);
        $entityHeadLine = Str::headline($this->entity);
        $entityHeadLinePlural = Str::plural(Str::headline($this->entity));

        $constants = $this->generateListRequestConstants();
        $multilingual = $this->detectMultilingualFields();

        return [
            '{{ module }}' => $this->module,
            '{{ entity }}' => $this->entity,
            '{{ entityPlural }}' => $entityPlural,
            '{{ entitySnakePlural }}' => $entitySnakePlural,
            '{{ entitySnakeUpper }}' => strtoupper($this->entitySnake),
            '{{ entityKebabPlural }}' => $entityKebabPlural,
            '{{ entityHeadLine }}' => $entityHeadLine,
            '{{ entityHeadLinePlural }}' => $entityHeadLinePlural,
            '{{ fillable }}' => $this->generateFillable(),
            '{{ casts }}' => $this->generateCasts(),
            '{{ rules }}' => $this->generateStoreRules(),
            '{{ update_rules }}' => $this->generateUpdateRules(),
            '{{ properties }}' => $this->generateDtoProperties(),
            '{{ assignments }}' => $this->generateDtoAssignments(),
            '{{ to_array }}' => $this->generateDtoArray(),
            '{{ resource_fields }}' => $this->generateResourceFields($foreignKeyNames, $hasManyRelationships),
            '{{ relationships }}' => $this->generateRelationships($hasManyRelationships),
            '{{ relation_types }}' => $this->generateRelationTypes($hasManyRelationships),
            '{{ relationship_names }}' => $this->getRelationships(),
            '{{ model_uses }}' => implode("\n", $this->getCustomUses()),

            '{{ list_sortable }}' => $constants['sortable'],
            '{{ list_relations }}' => $constants['relations'],
            '{{ list_filters }}' => $constants['filters'],
            '{{ list_searchable }}' => $constants['searchable'],
            '{{ list_fieldable }}' => $constants['fieldable'],
            '{{ multilingual_fields }}' => $multilingual ? $this->generateMultilingualDisplay($multilingual) : '',
            '{{ use_spatie_permission }}' => $this->isSpatiePermission() ? "use Spatie\Permission\Models\Permission as SpatiePermission;\n" : '',
            '{{ extends_spatie }}' => $this->isSpatiePermission() ? 'extends SpatiePermission' : 'extends Model',
            '{{ cast_ids }}' => $this->generateCastIds(),
            '{{ boolean_fields }}' => $this->generateBooleanFields(),
        ];
    }

    private function getForeignKeyNames(): array
    {
        return collect($this->fields)
            ->filter(fn ($f): bool => $f['relationship'] && $f['relationship']['type'] === 'BelongsTo')
            ->pluck('name')
            ->toArray();
    }

    private function getHasManyRelationships(): array
    {
        return collect($this->fields)
            ->filter(fn ($f): bool => $f['relationship'] && $f['relationship']['type'] === 'HasMany')
            ->pluck('relationship.name')
            ->toArray();
    }

    private function getRelationships(): string
    {
        return collect($this->fields)
            ->filter(fn ($f) => $f['relationship'])
            ->pluck('relationship.name')
            ->map(fn ($name): string => "'{$name}'")
            ->unique()
            ->implode(', ');
    }

    private function generateFillable(): string
    {
        return collect($this->fields)
            ->filter(fn ($f): bool => !$f['relationship'] || $f['relationship']['type'] !== 'HasMany')
            ->map(fn ($f): string => "        '{$f['name']}',")
            ->unique()
            ->implode("\n");
    }

    private function generateCasts(): string
    {
        return collect($this->fields)
            ->filter(fn ($f) => $f['cast'])
            ->map(fn ($f): string => "        '{$f['name']}' => '{$f['cast']}',")
            ->unique()
            ->implode("\n");
    }

    private function generateStoreRules(): string
    {
        return collect($this->fields)
            ->filter(fn ($f): bool => !$f['relationship'] || $f['relationship']['type'] === 'BelongsTo')
            ->map(function (array $field): string {
                $rule = $field['required'] ? 'required|' : 'nullable|';
                $rule .= $field['rule'];
                if ($field['unique']) {
                    $rule .= "|unique:{$field['table']},{$field['name']}";
                }
                if ($field['relationship'] && $field['relationship']['type'] === 'BelongsTo') {
                    $relatedTable = Str::plural(Str::snake($field['relationship']['model']));
                    $rule .= "|exists:{$relatedTable},id";
                }

                return "            '{$field['camel']}' => '{$rule}',";
            })
            ->unique()
            ->implode("\n");
    }

    private function generateUpdateRules(): string
    {
        return collect($this->fields)
            ->filter(fn ($f): bool => !$f['relationship'] || $f['relationship']['type'] === 'BelongsTo')
            ->map(function (array $field): string {
                $requiredRule = $field['required'] ? 'required' : 'nullable';
                $rule = $field['rule'];
                if ($field['relationship'] && $field['relationship']['type'] === 'BelongsTo') {
                    $relatedTable = Str::plural(Str::snake($field['relationship']['model']));
                    $rule .= "|exists:{$relatedTable},id";
                }
                if ($field['unique']) {
                    $ruleParts = explode('|', $rule);
                    array_unshift($ruleParts, $requiredRule);
                    $ruleParts[] = "Rule::unique(table: '{$field['table']}', column: '{$field['name']}')->ignore(id: \$this->route(param: '{$this->entitySnake}'))";

                    $formattedRules = implode(",\n                ", array_map(fn ($r): string => Str::startsWith($r, 'Rule::') ? $r : "'{$r}'", $ruleParts));

                    return <<<PHP
            '{$field['camel']}' => [
                {$formattedRules},
            ],
PHP;
                }

                return "            '{$field['camel']}' => '{$requiredRule}|{$rule}',";
            })
            ->unique()
            ->implode("\n");
    }

    private function generateDtoProperties(): string
    {
        $mandatory = [];
        $withDefaults = [];
        $nullableFields = $this->constants['NULLABLE_FIELDS'] ?? [];

        foreach ($this->fields as $f) {
            $isBoolean = in_array($f['name'], $this->constants['BOOLEAN_FIELDS'] ?? [], true);
            $hasDefault = $f['has_default'] ?? false;
            $isHasMany = ($f['relationship']['type'] ?? '') === 'HasMany';
            $isNullable = in_array($f['name'], $nullableFields, true);

            $type = $isHasMany ? 'array' : ($f['type'] ?? 'mixed');
            if ($isNullable && !$isHasMany) {
                $type = "?{$type}";
            }

            $f = array_merge($f, ['type' => $type]);

            if ($isBoolean || $hasDefault || $isHasMany) {
                $withDefaults[] = $f;
            } else {
                $mandatory[] = $f;
            }
        }

        $lines = [];

        foreach ($mandatory as $f) {
            $lines[] = '    public '.$f['type']." \${$f['camel']},";
        }

        foreach ($withDefaults as $withDefault) {
            $default = $this->getDefaultValue($withDefault);
            $lines[] = '    public '.$withDefault['type']." \${$withDefault['camel']} = {$default},";
        }

        return implode("\n", $lines);
    }

    private function generateDtoAssignments(): string
    {
        $castIds = $this->constants['CAST_IDS'] ?? [];
        $nullableFields = $this->constants['NULLABLE_FIELDS'] ?? [];

        $mandatory = [];
        $withDefaults = [];

        foreach ($this->fields as $f) {
            $isBoolean = in_array($f['name'], $this->constants['BOOLEAN_FIELDS'] ?? [], true);
            $hasDefault = $f['has_default'] ?? false;
            $isHasMany = ($f['relationship']['type'] ?? '') === 'HasMany';

            if ($isBoolean || $hasDefault || $isHasMany) {
                $withDefaults[] = $f;
            } else {
                $mandatory[] = $f;
            }
        }

        $lines = [];

        foreach (array_merge($mandatory, $withDefaults) as $f) {
            $key = $f['camel'];
            $value = in_array($key, $castIds, true) ? "\$ids['{$key}']" : "\$data['{$key}']";
            $default = $this->getDefaultValue($f);

            if (in_array($f['name'], $nullableFields, true) && !$f['has_default'] ?? false) {
                $default = 'null';
            }

            $lines[] = "        {$key}: {$value} ?? {$default},";
        }

        return implode("\n", $lines);
    }

    /**
     * Determine the appropriate default value for a field based on its type and migration default.
     */
    private function getDefaultValue(array $field): string // NOSONAR
    {
        $isHasMany = ($field['relationship']['type'] ?? '') === 'HasMany';
        if ($isHasMany) {
            return '[]';
        }

        if (empty($field['has_default'])) {
            return 'null';
        }

        $default = $field['default'] ?? null;
        if ($default === null) {
            return 'null';
        }

        return match ($field['type']) {
            'bool' => ($default === 'true' || $default === '1') ? 'true' : 'false',
            'int' => is_numeric($default) ? (string) $default : '0',
            'string' => $this->isStringEnum($field) ? "'{$default}'" : 'null',
            'datetime' => 'null',
            default => 'null',
        };
    }

    private function isStringEnum(array $field): bool
    {
        return ($field['cast'] ?? null) === 'string' &&
            (!empty($field['rule']) && (
                str_contains((string) $field['rule'], 'in:') || $field['rule'] === 'string'
            ));
    }

    private function generateDtoArray(): string
    {
        return collect($this->fields)
            ->filter(fn ($f): bool => !$f['relationship'] || $f['relationship']['type'] === 'BelongsTo')
            ->map(fn ($f): string => "            '{$f['name']}' => \$this->{$f['camel']},")
            ->unique()
            ->implode("\n");
    }

    private function generateResourceFields(array $foreignKeyNames, array $hasManyRelationships): string
    {
        $fields = collect($this->fields)
            ->filter(fn ($f): bool => !$f['relationship'] && !in_array($f['name'], $foreignKeyNames))
            ->map(fn ($f): string => "            '{$f['camel']}' => \$this->{$f['name']},")
            ->merge(
                collect($this->fields)
                    ->filter(fn ($f): bool => $f['relationship'] && $f['relationship']['type'] === 'BelongsTo')
                    ->map(fn ($f): string => "            '{$f['relationship']['name']}' => \$this->{$f['relationship']['name']},")
            )
            ->merge(
                collect($hasManyRelationships)
                    ->map(fn ($rel): string => "            '{$rel}' => \$this->{$rel},")
            )
            ->unique()
            ->toArray();

        array_unshift($fields, "            'id' => \$this->id,");
        $fields[] = "            'createdAt' => \$this->created_at?->toIso8601String(),";
        $fields[] = "            'updatedAt' => \$this->updated_at?->toIso8601String(),";

        return implode("\n", array_unique($fields));
    }

    private function generateRelationships(array $hasManyRelationships): string
    {
        $relationships = collect($this->fields)
            ->filter(fn ($f) => $f['relationship'])
            ->map(function (array $field): string {
                $method = $field['relationship']['name'];
                $model = $field['relationship']['model'];
                $type = $field['relationship']['type'];
                $function = $field['relationship']['function'];

                return <<<PHP
    public function {$method}(): {$type}
    {
        return \$this->{$function}({$model}::class);
    }

PHP;
            })
            ->unique();

        if ($hasManyRelationships !== []) {
            $checks = array_map(fn ($rel): string => "\$this->{$rel}()->exists()", $hasManyRelationships);
            $checkString = implode(' || ', $checks);
            $relationships[] = <<<PHP
    public function hasRelatedRecords(): bool
    {
        return {$checkString};
    }

PHP;
        }

        return $relationships->implode("\n");
    }

    private function generateRelationTypes(array $hasManyRelationships): string
    {
        return collect($this->fields)
            ->filter(fn ($f) => $f['relationship'])
            ->pluck('relationship.type')
            ->merge($hasManyRelationships === [] ? [] : ['HasMany'])
            ->unique()
            ->map(fn ($type): string => "use Illuminate\\Database\\Eloquent\\Relations\\{$type};")
            ->implode("\n");
    }

    private function getCustomUses(): array
    {
        return collect($this->fields)
            ->filter(fn ($f) => $f['relationship'])
            ->pluck('relationship.model')
            ->unique()
            ->map(fn (string $model): string => $this->resolveModelUseStatement($model))
            ->filter()
            ->values()
            ->toArray();
    }

    private function resolveModelUseStatement(string $model): string
    {
        $base = base_path();
        $useStatement = null;

        $sameModulePath = "{$base}/app/Modules/{$this->module}/Domain/Entities/{$model}.php";
        if (file_exists($sameModulePath)) {
            $useStatement = "use App\\Modules\\{$this->module}\\Domain\\Entities\\{$model};";
        }

        if (!$useStatement) {
            foreach (File::directories("{$base}/app/Modules") as $moduleDir) {
                $moduleName = basename((string) $moduleDir);
                $modulePath = "{$base}/app/Modules/{$moduleName}/Domain/Entities/{$model}.php";

                if (file_exists($modulePath)) {
                    $useStatement = "use App\\Modules\\{$moduleName}\\Domain\\Entities\\{$model};";
                    break;
                }
            }
        }

        if (!$useStatement) {
            $globalModelPath = "{$base}/app/Models/{$model}.php";
            if (file_exists($globalModelPath)) {
                $useStatement = "use App\\Models\\{$model};";
            }
        }

        return $useStatement ?? "use App\\Models\\{$model};";
    }

    // new changes
    private function generateListRequestConstants(): array
    {
        // Ensure defaults exist even if not provided
        $c = array_merge([
            'SORTABLE_COLUMNS' => [],
            'FILTERS' => [],
            'SEARCHABLE_COLUMNS' => [],
            'FIELDABLE_COLUMNS' => [],
            'CAST_IDS' => [],
            'BOOLEAN_FIELDS' => [],
        ], $this->constants ?? []);

        return [
            'sortable' => $this->exportArray($c['SORTABLE_COLUMNS']),
            'relations' => $this->exportArray($this->getBelongsToRelationNames()),
            'filters' => $this->exportArray($c['FILTERS']),
            'searchable' => $this->exportArray($c['SEARCHABLE_COLUMNS']),
            'fieldable' => $this->exportArray($c['FIELDABLE_COLUMNS']),
        ];
    }

    private function isSpatiePermission(): bool
    {
        $names = array_column($this->fields, 'name');

        return in_array('name', $names) && in_array('guard_name', $names);
    }

    private function detectMultilingualFields(): ?array
    {
        $patterns = ['_en', '_ps', '_dr'];
        $groups = [];
        foreach ($this->fields as $field) {
            foreach ($patterns as $pattern) {
                if (str_ends_with((string) $field['name'], $pattern)) {
                    $base = str_replace($pattern, '', $field['name']);
                    $groups[$base][] = $field['name'];
                }
            }
        }

        return $groups === [] ? null : $groups;
    }

    private function generateMultilingualDisplay(array $groups): string
    {
        $code = '';
        foreach ($groups as $base => $cols) {
            $camel = Str::camel($base);
            $code .= "        \${$camel} = \$this->{$cols[0]}; // fallback en\n";

            foreach (['ps', 'dr'] as $lang) {
                $suffix = "_{$lang}";
                $index = array_search($base.$suffix, $cols);
                $col = $index !== false ? $cols[$index] : null;
                if ($col) {
                    $code .= "        if (\$locale === '{$lang}') \${$camel} = \$this->{$col};\n";
                }
            }

            $displayKey = 'displayName'.ucfirst($camel);
            $code .= "        '{$displayKey}' => \${$camel},\n";
        }

        return $code;
    }

    private function extractConstantsFromMigration(): array
    {
        $base = base_path('database/migrations');
        $filePattern = sprintf('%s/*%s*.php', $base, Str::snake(Str::plural($this->entity)));

        $migrationFile = collect(glob($filePattern))->first();
        if (!$migrationFile || !file_exists($migrationFile)) {
            return [];
        }

        $content = file_get_contents($migrationFile);

        $constants = [
            'SORTABLE_COLUMNS' => [],
            'FILTERS' => [],
            'SEARCHABLE_COLUMNS' => [],
            'FIELDABLE_COLUMNS' => [],
            'BOOLEAN_FIELDS' => [],
            'CAST_IDS' => [],
            'NULLABLE_FIELDS' => [],
        ];

        foreach (array_keys($constants) as $name) {
            if (preg_match("/private const {$name}\s*=\s*(\[.*?\]);/s", $content, $matches)) {
                try {
                    $constants[$name] = eval("return {$matches[1]};");
                } catch (\Throwable) {
                    $constants[$name] = [];
                }
            }
        }

        $nullable = [];
        if (preg_match_all('/\$table->\w+\(\'(.*?)\'\)->nullable\(\)/', $content, $matches)) {
            $nullable = $matches[1];
        }
        $constants['NULLABLE_FIELDS'] = $nullable;

        return $constants;
    }

    private function generateCastIds(): string
    {
        return collect($this->constants['CAST_IDS'] ?? [])
            ->map(fn ($id): string => "'{$id}'")
            ->implode(', ');
    }

    private function generateBooleanFields(): string
    {
        $fields = $this->constants['BOOLEAN_FIELDS'] ?? [];

        return collect($fields)->map(fn ($f): string => "'{$f}'")->implode(', ');
    }

    private function getBelongsToRelationNames(): ?array
    {
        $names = collect($this->fields)
            ->filter(fn ($f): bool => ($f['relationship']['type'] ?? '') === 'BelongsTo')
            ->pluck('relationship.name')
            ->all();

        return empty($names) ? null : $names;
    }

    private function exportArray(?array $array): string
    {
        if ($array === null || $array === []) {
            return '[]';
        }

        // If all keys are numeric, flatten it
        $isNumeric = array_keys($array) === range(0, count($array) - 1);
        if ($isNumeric) {
            $items = array_map(
                fn ($v): ?string => is_array($v) ? $this->exportArray($v) : var_export($v, true),
                $array
            );

            return "[\n            ".implode(",\n            ", $items).",\n        ]";
        }

        // If associative, export as key => value
        $items = [];
        foreach ($array as $key => $value) {
            $formattedValue = is_array($value)
                ? $this->exportArray($value)
                : var_export($value, true);

            $items[] = var_export($key, true).' => '.$formattedValue;
        }

        return "[\n            ".implode(",\n            ", $items).",\n        ]";
    }
}
