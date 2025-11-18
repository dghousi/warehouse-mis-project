<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use Carbon\Carbon;

final class DataMapper
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    public function mapRowData($row, array $columns, string $targetTable, array $validIds): array
    {
        $data = [];

        foreach ($columns as $old => $mapping) {
            $targetColumns = $this->resolveTargetColumns($mapping);
            if ($targetColumns === []) {
                continue;
            }
            if (count($targetColumns) === 1 && empty($targetColumns[0])) {
                continue;
            }

            $value = $row->$old ?? null;
            $value = $this->applyMappingRules($value, $mapping, $row, $validIds);
            $value = $this->applyTableSpecificRules($value, $targetColumns, $targetTable, $mapping, $row, $validIds);

            $this->assignColumnValues($data, $targetColumns, $value);
        }

        return $data;
    }

    private function assignColumnValues(array &$data, array $targetColumns, $value): void
    {
        foreach ($targetColumns as $targetColumn) {
            if (!empty($targetColumn)) {
                $data[$targetColumn] = ($targetColumn === 'created_at' && $value === null)
                    ? Carbon::now()->toDateTimeString()
                    : $value;
            }
        }
    }

    private function resolveTargetColumns($mapping): array
    {
        if (is_array($mapping) && isset($mapping['target'])) {
            return (array) $mapping['target'];
        }

        return (array) $mapping;
    }

    private function applyMappingRules($value, $mapping, $row, array $validIds)
    {
        if (!is_array($mapping)) {
            return $value;
        }

        $result = $value;

        if (isset($mapping['map']) && array_key_exists($value, $mapping['map'])) {
            $result = $mapping['map'][$value];
        } elseif (($value === null || $value === '') && isset($mapping['default'])) {
            $result = $this->resolveDefaultValue($mapping['default'], $row);
        } elseif (isset($mapping['validate']) && is_callable($mapping['validate'])) {
            $result = $mapping['validate']($value, $validIds);
        }

        return $result;
    }

    private function resolveDefaultValue($default, $row)
    {
        if (is_callable($default)) {
            return $default($row);
        }

        return $default;
    }

    private function applyTableSpecificRules($value, array $targetColumns, string $targetTable, $mapping, $row, array $validIds)
    {
        if (!is_array($mapping) || !in_array($targetTable, ['users', 'roles'])) {
            return $value;
        }

        if ($targetTable === 'users') {
            return $this->applyUsersTableRules($value, $targetColumns, $mapping, $row, $validIds);
        }

        return $this->applyRolesTableRules($value, $targetColumns, $mapping, $row);
    }

    private function applyUsersTableRules($value, array $targetColumns, array $mapping, $row, array $validIds)
    {
        $nonNullableColumns = ['first_name', 'job_title', 'password', 'locale', 'rights', 'status', 'notifications', 'enabled'];
        $numericColumns = ['report_to_id', 'main_organization_id'];
        $foreignKeyColumns = ['report_to_id', 'created_by', 'updated_by', 'deleted_by'];

        $result = $value;

        if (array_intersect($targetColumns, $nonNullableColumns) && ($value === null || $value === '')) {
            $result = $this->resolveDefaultValue($mapping['default'] ?? 'Unknown', $row);
        } elseif (array_intersect($targetColumns, $numericColumns) && $value === null) {
            $result = 0;
        } elseif (array_intersect($targetColumns, $foreignKeyColumns) && $value !== null && !in_array($value, $validIds)) {
            $result = in_array('created_by', $targetColumns) ? null : 0;
        }

        return $result;
    }

    private function applyRolesTableRules($value, array $targetColumns, array $mapping, $row)
    {
        $nonNullableColumns = ['name', 'display_name_en', 'display_name_dr', 'display_name_ps', 'guard_name'];
        $result = $value;

        if (array_intersect($targetColumns, $nonNullableColumns) && ($value === null || $value === '')) {
            $result = $this->resolveDefaultValue($mapping['default'] ?? 'Unknown', $row);
        } elseif (in_array('module_id', $targetColumns) && $value === null) {
            $result = 0;
        }

        return $result;
    }
}
