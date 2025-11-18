<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Repositories;

use App\Modules\Common\Application\Exceptions\ApiException;
use App\Modules\Common\Infrastructure\Repositories\BaseEloquentRepository;
use App\Modules\UserManagement\Domain\Entities\Permission;
use App\Modules\UserManagement\Domain\Repositories\PermissionRepositoryInterface;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Permission\ListPermissionsRequest;
use Spatie\QueryBuilder\QueryBuilder;

final class EloquentPermissionRepository extends BaseEloquentRepository implements PermissionRepositoryInterface
{
    protected string $model = Permission::class;

    protected string $requestClass = ListPermissionsRequest::class;

    public function find(int $id): Permission
    {
        return QueryBuilder::for($this->model)
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->requestClass::getAllowedRelations())
            ->findOrFail($id);
    }

    public function findTrashed(int $id): ?Permission
    {
        return QueryBuilder::for($this->model)
            ->onlyTrashed()
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->requestClass::getAllowedRelations())
            ->find($id);
    }

    public function create(array $data): Permission
    {
        return Permission::create(attributes: $data);
    }

    public function update(int $id, array $data): Permission
    {
        $permission = $this->find($id);

        $permission->update(attributes: $data);

        return $permission;
    }

    public function delete(int $id): void
    {
        $permission = $this->find(id: $id);

        $this->guardAgainstRelatedRecords($permission);

        $permission->delete();
    }

    public function forceDelete(int $id): void
    {
        $permission = Permission::withTrashed()->findOrFail($id);

        $this->guardAgainstRelatedRecords($permission);

        $permission->forceDelete();
    }

    public function restore(int $id): Permission
    {
        $permission = Permission::withTrashed()->findOrFail($id);

        $permission->restore();

        return $permission;
    }

    private function getAllowedFields(): array
    {
        $fields = request()->input('fields');
        if (!$fields) {
            return [];
        }

        $allowed = array_keys($this->requestClass::getFieldableColumns());
        $requested = array_filter(explode(',', (string) $fields));

        return array_intersect($requested, $allowed);
    }

    private function guardAgainstRelatedRecords(Permission $permission): void
    {
        if (method_exists($permission, 'hasRelatedRecords') && $permission->hasRelatedRecords()) {
            throw new ApiException(
                errorCode: 'PERMISSION_IN_USE',
                message: 'Cannot delete permission because it has related records.',
                httpCode: 409
            );
        }
    }
}
