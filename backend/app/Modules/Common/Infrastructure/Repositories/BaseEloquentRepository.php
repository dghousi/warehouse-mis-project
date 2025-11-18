<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Repositories;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

abstract class BaseEloquentRepository
{
    protected string $model;

    protected string $requestClass;

    private const CACHE_TTL = 1440;

    public function paginate(QuerySpecification $querySpecification): LengthAwarePaginator
    {
        $key = $this->cacheKey($querySpecification);
        $tag = $this->cacheTag();

        $cache = $this->supportsTagging()
            ? Cache::tags([$tag])
            : Cache::store();

        return $cache->remember($key, now()->addMinutes(self::CACHE_TTL), function () use ($querySpecification) {
            $sorts = $this->buildAllowedSorts();

            $default = $this->buildDefaultSort($querySpecification);

            $searchable = $this->searchableColumns();
            $searchFields = $querySpecification->searchFields;

            $allowedFieldKeys = array_keys($searchable);

            $fieldsToSearch = $searchFields !== []
                ? array_intersect($searchFields, $allowedFieldKeys)
                : $allowedFieldKeys;

            $queryBuilder = QueryBuilder::for($this->getModel())
                ->allowedFilters($this->filters())
                ->allowedSorts($sorts)
                ->defaultSort($default);

            if ($querySpecification->fieldsCamel) {
                $queryBuilder->allowedFields($querySpecification->fieldsCamel);
            }

            $queryBuilder->allowedIncludes($this->requestClass::getAllowedRelations());

            if ($querySpecification->search && $fieldsToSearch) {
                $this->applySearch($queryBuilder, $querySpecification->search, $fieldsToSearch);
            }

            return $queryBuilder->paginate(
                perPage: $querySpecification->perPage,
                columns: ['*'],
                pageName: 'page',
                page: $querySpecification->page
            )->appends(request()->query());
        });
    }

    public function invalidateCache(): void
    {
        if ($this->supportsTagging()) {
            Cache::tags([$this->cacheTag()])->flush();
        } else {
            Cache::flush();
        }
    }

    protected function applySearch(QueryBuilder $queryBuilder, ?string $search, array $fields): void
    {
        if (!$search || !$fields) {
            return;
        }

        $words = preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY);
        if (!$words) {
            return;
        }

        $searchable = $this->searchableColumns();

        $dbColumns = [];
        foreach ($fields as $field) {
            if (isset($searchable[$field])) {
                $cols = $searchable[$field];
                $dbColumns = array_merge($dbColumns, is_array($cols) ? $cols : [$cols]);
            }
        }

        if ($dbColumns === []) {
            return;
        }

        $queryBuilder->where(function (EloquentBuilder $eloquentBuilder) use ($words, $dbColumns): void {
            foreach ($words as $word) {
                $eloquentBuilder->where(function (EloquentBuilder $eloquentBuilder) use ($dbColumns, $word): void {
                    [$sql, $bindings] = $this->buildLikeQuery($dbColumns, $word);
                    $eloquentBuilder->whereRaw($sql, $bindings);
                });
            }
        });
    }

    protected function getModel(): string
    {
        if (!class_exists($this->model) || !is_subclass_of($this->model, Model::class)) {
            throw new \InvalidArgumentException("{$this->model} is not a valid Eloquent model");
        }

        return $this->model;
    }

    private function buildLikeQuery(array $columns, string $word): array
    {
        $parts = array_map(fn ($col): string => "LOWER({$col}) LIKE LOWER(?)", $columns);

        return [implode(' OR ', $parts), array_fill(0, count($columns), "%{$word}%")];
    }

    private function filters(): array
    {
        $allowed = [];

        foreach ($this->requestClass::getFilters() as $snakeKey => $filter) {
            if (in_array($snakeKey, ['search', 'trashed'], true)) {
                continue;
            }

            $camelKey = Str::camel($snakeKey);

            $allowed[] = AllowedFilter::exact($camelKey, $snakeKey);
        }

        $allowed[] = AllowedFilter::trashed();

        return $allowed;
    }

    private function cacheKey(QuerySpecification $querySpecification): string
    {
        $params = get_object_vars($querySpecification);
        ksort($params);

        return $this->cacheTag().'_page_'.hash('sha256', serialize($params));
    }

    private function cacheTag(): string
    {
        return Str::plural($this->entityName());
    }

    private function entityName(): string
    {
        return Str::snake(class_basename($this->model));
    }

    private function supportsTagging(): bool
    {
        $driver = config('cache.stores.'.config('cache.default').'.driver');

        return in_array($driver, ['redis', 'memcached', 'dynamodb']);
    }

    private function buildAllowedSorts(): array
    {
        $sorts = [];
        foreach ($this->requestClass::getSortableColumns() as $sortableColumn) {
            $sorts[] = $sortableColumn;
            $sorts[] = "-{$sortableColumn}";
        }

        return $sorts;
    }

    private function buildDefaultSort(QuerySpecification $querySpecification): string
    {
        return $querySpecification->sortDirection === 'desc'
            ? "-{$querySpecification->sortBy}"
            : $querySpecification->sortBy;
    }

    protected function searchableColumns(): array
    {
        return $this->requestClass::getSearchableColumns();
    }
}
