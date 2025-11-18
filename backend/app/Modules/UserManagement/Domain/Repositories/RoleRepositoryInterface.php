<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Repositories;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\UserManagement\Domain\Entities\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface
{
    public function paginate(QuerySpecification $querySpecification): LengthAwarePaginator;

    public function find(int $id): Role;

    public function findTrashed(int $id): ?Role;

    public function create(array $data): Role;

    public function update(int $id, array $data): Role;

    public function delete(int $id): void;

    public function forceDelete(int $id): void;

    public function restore(int $id): Role;

    public function assignPermissions(int $roleId, array $permissions): Role;

    public function invalidateCache(): void;
}
