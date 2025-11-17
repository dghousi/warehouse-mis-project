<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Services;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\UserManagement\Domain\Entities\Permission;
use App\Modules\UserManagement\Domain\Repositories\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class PermissionService
{
    public function __construct(private PermissionRepositoryInterface $permissionRepository) {}

    public function listPermissions(QuerySpecification $querySpecification): LengthAwarePaginator
    {
        return $this->permissionRepository->paginate($querySpecification);
    }

    public function getPermission(int $id): Permission
    {
        return $this->permissionRepository->find(id: $id);
    }

    public function restorePermission(int $id): Permission
    {
        return tap(
            $this->permissionRepository->restore($id),
            fn () => $this->permissionRepository->invalidateCache()
        );
    }

    public function createPermission(array $data): Permission
    {
        return tap(
            $this->permissionRepository->create(data: $data),
            fn () => $this->permissionRepository->invalidateCache()
        );
    }

    public function updatePermission(int $id, array $data): Permission
    {
        return tap(
            $this->permissionRepository->update(id: $id, data: $data),
            fn () => $this->permissionRepository->invalidateCache()
        );
    }

    public function deletePermission(int $id): void
    {
        $this->permissionRepository->delete(id: $id);

        $this->permissionRepository->invalidateCache();
    }

    public function forceDeletePermission(int $id): void
    {
        $permission = $this->permissionRepository->findTrashed($id) ?? $this->permissionRepository->find($id);
        $this->permissionRepository->forceDelete($permission->id);

        $this->permissionRepository->invalidateCache();
    }

    public function bulkDeletePermissions(array $ids): array
    {
        $deleted = [];
        $skipped = [];

        foreach ($ids as $id) {
            $permission = $this->permissionRepository->find($id);

            if (method_exists($permission, 'hasRelatedRecords') && $permission->hasRelatedRecords()) {
                $skipped[] = $id;

                continue;
            }

            $this->permissionRepository->delete(id: $id);
            $deleted[] = $id;
        }

        $this->permissionRepository->invalidateCache();

        return ['deleted' => $deleted, 'skipped' => $skipped];
    }
}
