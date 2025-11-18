<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

abstract class BaseListRequest extends FormRequest
{
    abstract protected static function sortableColumns(): array;

    abstract protected static function allowedRelations(): array;

    abstract protected static function filters(): array;

    abstract protected static function searchableColumns(): array;

    abstract protected static function fieldableColumns(): array;

    public static function getSortableColumns(): array
    {
        return static::sortableColumns();
    }

    public static function getAllowedRelations(): array
    {
        return static::allowedRelations();
    }

    public static function getFilters(): array
    {
        return static::filters();
    }

    public static function getSearchableColumns(): array
    {
        return static::searchableColumns();
    }

    public static function getFieldableColumns(): array
    {
        return static::fieldableColumns();
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $filters = $this->input('filter', []);
        if (is_array($filters)) {
            $allowed = array_keys(static::filters());
            $norm = [];
            foreach ($filters as $k => $v) {
                $snake = Str::snake($k);
                if (in_array($snake, $allowed, true)) {
                    $norm[$snake] = $v;
                }
            }
            if ($norm !== $filters) {
                $this->merge(['filter' => $norm]);
            }
        }

        if ($this->filled('sortBy')) {
            $snake = Str::snake($this->input('sortBy'));
            if (in_array($snake, array_keys(static::sortableColumns()), true)) {
                $this->merge(['sortBy' => $snake]);
            }
        }
    }

    public function rules(): array
    {
        $sortables = array_keys(static::sortableColumns());
        $relations = static::allowedRelations();
        $searchables = array_keys(static::searchableColumns());

        $rules = [
            'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sortBy' => ['nullable', 'string', Rule::in($sortables)],
            'sortDirection' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'include' => ['nullable', 'array'],
            'include.*' => ['string', Rule::in($relations)],
            'filter' => ['nullable', 'array'],
            'filter.trashed' => ['nullable', 'in:only,with,without'],
            'search' => ['nullable', 'string', 'max:255'],
            'searchFields' => ['nullable', 'array'],
            'searchFields.*' => ['string', Rule::in($searchables)],
            'fields' => ['nullable', 'string'],
        ];

        foreach (static::filters() as $key => $allowed) {
            if (in_array($key, ['search', 'trashed'], true)) {
                continue;
            }

            $rules["filter.{$key}"] = ['nullable', 'array'];
            $rules["filter.{$key}.*"] = [
                is_int(reset($allowed)) ? 'integer' : 'string',
                Rule::in($allowed),
            ];
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if ($key) {
            return $data ?? $default;
        }

        $sortBy = $data['sortBy'] ?? 'createdAt';
        $sortBySnake = static::sortableColumns()[$sortBy] ?? 'created_at';

        $searchFields = $data['searchFields'] ?? array_keys(static::searchableColumns());

        $searchFields = array_values(
            array_unique(
                array_filter(
                    array_map(strval(...), Arr::flatten($searchFields))
                )
            )
        );

        $fieldable = static::fieldableColumns();

        $fieldsInput = $data['fields'] ?? null;
        $fieldsCamel = $fieldsInput ? preg_split('/\s*,\s*/', trim((string) $fieldsInput)) : null;
        $fieldsSnake = $fieldsCamel
            ? array_values(array_filter(array_map(fn ($f) => $fieldable[$f] ?? null, $fieldsCamel)))
            : null;

        if ($fieldsSnake === []) {
            $fieldsSnake = null;
            $fieldsCamel = null;
        }

        return [
            'perPage' => $data['perPage'] ?? 10,
            'page' => $data['page'] ?? 1,
            'sortBy' => $sortBySnake,
            'sortDirection' => $data['sortDirection'] ?? 'desc',
            'include' => $data['include'] ?? [],
            'filters' => $data['filter'] ?? [],
            'search' => $data['search'] ?? null,
            'searchFields' => $searchFields,
            'fields' => $fieldsSnake,
            'fieldsCamel' => $fieldsCamel,
        ];
    }
}
