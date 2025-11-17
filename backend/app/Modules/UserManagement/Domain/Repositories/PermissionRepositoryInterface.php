<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Repositories;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\UserManagement\Domain\Entities\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PermissionRepositoryInterface
{
    public function paginate(QuerySpecification $querySpecification): LengthAwarePaginator;

    public function find(int $id): Permission;

    public function findTrashed(int $id): ?Permission;

    public function create(array $data): Permission;

    public function update(int $id, array $data): Permission;

    public function delete(int $id): void;

    public function forceDelete(int $id): void;

    public function restore(int $id): Permission;

    public function invalidateCache(): void;
}
