<?php

declare(strict_types=1);

namespace App\Modules\Common\Application\DTOs;

final readonly class QuerySpecification
{
    public function __construct(
        public ?string $search = null,
        public array $filters = [],
        public int $perPage = 10,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc',
        public array $include = [],
        public int $page = 1,
        public array $searchFields = [],
        public ?array $fields = null,
        public ?array $fieldsCamel = null,
    ) {}

    public static function fromArray(array $validated): self
    {
        return new self(
            search: $validated['search'] ?? null,
            filters: $validated['filters'] ?? [],
            perPage: (int) ($validated['perPage'] ?? 10),
            sortBy: $validated['sortBy'] ?? 'created_at',
            sortDirection: $validated['sortDirection'] ?? 'desc',
            include: $validated['include'] ?? [],
            page: (int) ($validated['page'] ?? 1),
            searchFields: $validated['searchFields'] ?? [],
            fields: $validated['fields'] ?? null,
            fieldsCamel: $validated['fieldsCamel'] ?? null,
        );
    }
}
