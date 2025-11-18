<?php

declare(strict_types=1);

namespace App\Console\Commands\Backend;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MigrationParser
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    public function parse(string $path): array
    {
        $content = File::get($path);
        $lines = collect(explode("\n", $content));
        $tableName = $this->extractTableName($lines);

        if (!$tableName) {
            return [];
        }

        $fields = [];
        $fields = array_merge($fields, $this->extractHasManyFields($tableName));

        $columnFields = $this->extractColumnFields($lines, $content, $tableName);
        $constants = $this->extractMigrationConstants($content);

        return [
            'table' => $tableName,
            'fields' => array_merge($columnFields, $fields),
            'constants' => $constants,
        ];
    }

    /**
     * Extract table name from migration lines.
     */
    protected function extractTableName(Collection $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match("/Schema::(create|table)\(['\"](\w+)['\"]/", (string) $line, $matches)) {
                return $matches[2];
            }
        }

        return null;
    }

    /**
     * Extract all HasMany relationships for the given table.
     */
    protected function extractHasManyFields(string $tableName): array
    {
        $relationships = $this->findHasManyRelationships($tableName);

        return array_map(fn ($table): array => [
            'name' => $table,
            'camel' => Str::camel($table),
            'rule' => null,
            'required' => false,
            'cast' => null,
            'type' => null,
            'unique' => false,
            'has_default' => false,
            'relationship' => [
                'type' => 'HasMany',
                'name' => Str::camel($table),
                'model' => Str::studly(Str::singular($table)),
                'function' => 'hasMany',
            ],
            'table' => $tableName,
        ], $relationships);
    }

    /**
     * Extract column field definitions from migration lines.
     */
    protected function extractColumnFields(Collection $lines, string $content, string $tableName): array
    {
        $fields = [];
        $processedFields = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if (!Str::contains($line, '$table->')) {
                continue;
            }

            if (preg_match("/\\\$table->(\w+)\(['\"](\w+)['\"](?:,\s*[^)]+)?\)/", $line, $matches)) {
                [$method, $name] = [$matches[1], $matches[2]];

                if (in_array($name, $processedFields)) {
                    continue;
                }

                $processedFields[] = $name;
                $fields[] = $this->buildColumnField($method, $name, $line, $content, $tableName);
            }
        }

        return $fields;
    }

    /**
     * Build a single column field metadata.
     */
    protected function buildColumnField(string $method, string $name, string $modifiers, string $content, string $tableName): array
    {
        $required = !Str::contains($modifiers, ['->nullable()', '->default(']);
        $hasDefault = Str::contains($modifiers, '->default(');
        $isUnique = Str::contains($modifiers, '->unique()');

        $field = [
            'name' => $name,
            'camel' => Str::camel($name),
            'rule' => 'string|max:255',
            'required' => $hasDefault ? false : $required,
            'cast' => null,
            'type' => 'string',
            'unique' => $isUnique,
            'has_default' => $hasDefault,
            'relationship' => null,
            'table' => $tableName,
            'default' => null,
        ];

        // Extract default value
        if ($hasDefault && preg_match("/->default\(['\"]?([^'\"]+)['\"]?\)/", $modifiers, $defaultMatch)) {
            $field['default'] = $defaultMatch[1];
        }

        // Apply field-specific transformations
        return $this->applyTypeCasting($field, $method, $modifiers, $content, $name);
    }

    protected function applyTypeCasting(array $field, string $method, string $modifiers, string $content, string $name): array
    {
        // Handle integer casts
        if (in_array($method, ['foreignId', 'unsignedBigInteger', 'integer', 'unsignedTinyInteger', 'unsignedInteger'])) {
            $field = $this->setIntType($field);
        }

        // Enum via array
        if ($method === 'enum' && preg_match("/\[(.*?)\]/", $modifiers, $enumMatch)) {
            $field = $this->setEnumArrayRule($field, $enumMatch[1]);
        }

        // Enum via comment
        if ($method === 'string' && preg_match("/->comment\(['\"]enum:(.*?)['\"]\)/", $modifiers, $enumComment)) {
            $field = $this->setEnumCommentRule($field, $enumComment[1]);
        }

        // Boolean type
        if ($method === 'boolean') {
            $field['cast'] = 'boolean';
            $field['type'] = 'bool';
            $field['rule'] = 'boolean';
        }

        // Date or timestamp type
        if (Str::contains($method, 'date') || $method === 'timestamp') {
            $field['cast'] = 'datetime';
            $field['type'] = 'string';
            $field['rule'] = 'date';
        }

        // Relationship check
        if (Str::contains($content, ['->foreign(', 'foreignId'])) {
            $field['relationship'] = $this->buildRelationship($name, $content);
        }

        return $field;
    }

    protected function setIntType(array $field): array
    {
        $field['cast'] = 'integer';
        $field['type'] = 'int';
        $field['rule'] = 'integer';

        return $field;
    }

    protected function setEnumArrayRule(array $field, string $enumValues): array
    {
        $options = array_map(trim(...), explode(',', str_replace("'", '', $enumValues)));
        $rule = 'in:'.implode(',', $options);
        $baseRule = $field['required'] ? "string|{$rule}" : "nullable|string|{$rule}";
        $field['rule'] = $baseRule;
        $field['cast'] = 'string';

        return $field;
    }

    protected function setEnumCommentRule(array $field, string $enumValues): array
    {
        $options = array_map(trim(...), explode(',', $enumValues));
        $rule = 'in:'.implode(',', $options);
        $baseRule = $field['required'] ? "string|{$rule}" : "nullable|string|{$rule}";
        $field['rule'] = $baseRule;
        $field['cast'] = 'string';

        return $field;
    }

    /**
     * Build BelongsTo relationship from foreign key definition.
     */
    protected function buildRelationship(string $name, string $content): ?array
    {
        $relatedTable = null;

        // Inline: $table->foreignId('user_id')->references('id')->on('users')
        if (preg_match("/\\\$table->(foreignId|unsignedBigInteger)\(['\"]{$name}['\"](?:,\s*[^)]+)?\)->references\(['\"](\w+)['\"]\)->on\(['\"](\w+)['\"]\)/", $content, $ref)) {
            $relatedTable = $ref[3];

            // Separate: $table->foreign('user_id')->references('id')->on('users')
        } elseif (preg_match("/\\\$table->foreign\(['\"]{$name}['\"]\)->references\(['\"](\w+)['\"]\)->on\(['\"](\w+)['\"]\)/", $content, $ref)) {
            $relatedTable = $ref[2];

            // Short: $table->foreignId('user_id')->constrained('users') or $table->foreignId('user_id')
        } elseif (preg_match("/\\\$table->foreignId\(['\"]{$name}['\"]\)(?:->constrained(?:\(['\"](\w+)['\"]\))?)?/", $content, $ref)) {
            $relatedTable = $ref[1] ?? Str::plural(Str::replaceLast('_id', '', $name));
        }

        if ($relatedTable) {
            return [
                'type' => 'BelongsTo',
                'name' => Str::camel(Str::replaceLast('_id', '', $name)),
                'model' => Str::studly(Str::singular($relatedTable)),
                'function' => 'belongsTo',
            ];
        }

        return null;
    }

    /**
     * Scan all migrations and find HasMany relationships pointing to this table.
     */
    protected function findHasManyRelationships(string $tableName): array
    {
        $migrationPath = base_path('database/migrations');
        $relationships = [];

        foreach (File::allFiles($migrationPath) as $file) {
            $content = File::get($file);
            $relatedTable = $this->extractTableNameFromContent($content);
            if (!$relatedTable) {
                continue;
            }

            if ($this->matchesExplicitForeignConstraint($content, $tableName)) {
                $relationships[] = $relatedTable;
            }

            if ($this->matchesConstrainedForeignId($content, $tableName)) {
                $relationships[] = $relatedTable;
            }

            $inferredMatches = $this->matchesInferredForeignId($content, $tableName);
            if ($inferredMatches !== []) {
                $relationships[] = $relatedTable;
            }
        }

        return array_unique($relationships);
    }

    /**
     * Extract table name from migration file content.
     */
    protected function extractTableNameFromContent(string $content): ?string
    {
        if (preg_match("/Schema::(create|table)\(['\"](\w+)['\"]/", $content, $match)) {
            return $match[2];
        }

        return null;
    }

    private function matchesExplicitForeignConstraint(string $content, string $tableName): bool
    {
        $pattern = "/
        \\\$table->foreign\(
            \s*['\"]([^'\"]+)['\"]\s*
        \)->references\(
            \s*['\"][^'\"]+['\"]\s*
        \)->on\(
            \s*['\"]{$tableName}['\"]\s*
        \)
    /x";

        return (bool) preg_match_all($pattern, $content);
    }

    private function matchesConstrainedForeignId(string $content, string $tableName): bool
    {
        return (bool) preg_match_all(
            "/\\\$table->foreignId\(['\"]\w+['\"]\)->constrained\(['\"]{$tableName}['\"]\)/",
            $content
        );
    }

    private function matchesInferredForeignId(string $content, string $tableName): array
    {
        preg_match_all(
            "/\\\$table->foreignId\(['\"](\w+)['\"]\)/",
            $content,
            $matches
        );

        return collect($matches[1] ?? [])
            ->filter(fn ($key): bool => Str::plural(Str::replaceLast('_id', '', $key)) === $tableName)
            ->all();
    }

    private function extractMigrationConstants(string $content): array // NOSONAR
    {
        $constants = [
            'SEARCHABLE_COLUMNS' => [],
            'FILTERS' => [],
            'SORTABLE_COLUMNS' => [],
            'FIELDABLE_COLUMNS' => [],
            'BOOLEAN_FIELDS' => [],
            'CAST_IDS' => [],
        ];

        if (preg_match_all("/private\s+const\s+(\w+)\s*=\s*\[(.*?)\];/s", $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1];
                $value = $match[2];

                if (str_starts_with($value, "'") || str_starts_with($value, '"')) {
                    // Simple array
                    preg_match_all("/'([^']+)'|\"([^\"]+)\"/", $value, $items);
                    $items = array_merge($items[1], $items[2]);
                    $constants[$name] = array_filter($items);
                } elseif (preg_match('/=>\s*[\[\{]/', $value)) {
                    // Associative
                    if ($name === 'SEARCHABLE_COLUMNS') {
                        preg_match_all("/'(\w+)'\s*=>\s*\[([^\]]+)\]/", $value, $pairs);
                        $result = [];
                        foreach ($pairs[1] as $i => $key) {
                            preg_match_all("/'([^']+)'/", $pairs[2][$i], $cols);
                            $result[$key] = $cols[1];
                        }
                        $constants[$name] = $result;
                    } elseif ($name === 'FIELDABLE_COLUMNS') {
                        preg_match_all("/'(\w+)'\s*=>\s*'([^']+)'/", $value, $pairs);
                        $result = [];
                        foreach ($pairs[1] as $i => $key) {
                            $result[$key] = $pairs[2][$i];
                        }
                        $constants[$name] = $result;
                    }
                }
            }
        }

        return $constants;
    }
}
