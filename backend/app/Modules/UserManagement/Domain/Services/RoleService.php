<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Services;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\UserManagement\Domain\Entities\Role;
use App\Modules\UserManagement\Domain\Repositories\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final readonly class RoleService
{
    public function __construct(private RoleRepositoryInterface $roleRepository) {}

    public function listRoles(QuerySpecification $querySpecification): LengthAwarePaginator
    {
        return $this->roleRepository->paginate($querySpecification);
    }

    public function getRole(int $id): Role
    {
        return $this->roleRepository->find(id: $id);
    }

    public function restoreRole(int $id): Role
    {
        return tap($this->roleRepository->restore($id), fn () => $this->roleRepository->invalidateCache());
    }

    public function createRole(array $data): Role
    {
        return tap(
            DB::transaction(function () use ($data): Role {
                $role = $this->roleRepository->create(data: $data);

                if (!empty($data['permissions'])) {
                    $role->syncPermissions($data['permissions']);
                }

                return $role;
            }),
            fn () => $this->roleRepository->invalidateCache()
        );
    }

    public function updateRole(int $id, array $data): Role
    {
        return tap(
            DB::transaction(callback: function () use ($id, $data): Role {
                $role = $this->roleRepository->update(id: $id, data: $data);

                if (isset($data['permissions'])) {
                    $role->syncPermissions(permissions: $data['permissions']);
                }

                return $role;
            }),
            fn () => $this->roleRepository->invalidateCache()
        );
    }

    public function deleteRole(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $this->roleRepository->delete(id: $id);
        });

        $this->roleRepository->invalidateCache();
    }

    public function forceDeleteRole(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $role = $this->roleRepository->findTrashed($id) ?? $this->roleRepository->find($id);
            $this->roleRepository->forceDelete($role->id);
        });

        $this->roleRepository->invalidateCache();
    }

    public function bulkDeleteRoles(array $ids): array
    {
        $deleted = [];
        $skipped = [];

        DB::transaction(function () use ($ids, &$deleted, &$skipped): void {
            foreach ($ids as $id) {
                $role = $this->roleRepository->find($id);

                if (method_exists($role, 'hasRelatedRecords') && $role->hasRelatedRecords()) {
                    $skipped[] = $id;

                    continue;
                }

                $this->roleRepository->delete(id: $id);
                $deleted[] = $id;
            }
        });

        $this->roleRepository->invalidateCache();

        return ['deleted' => $deleted, 'skipped' => $skipped];
    }

    public function assignPermissions(int $roleId, array $permissionIds): Role
    {
        $role = $this->roleRepository->find(id: $roleId);
        $role->syncPermissions(permissions: $permissionIds);

        return $role;
    }
}
