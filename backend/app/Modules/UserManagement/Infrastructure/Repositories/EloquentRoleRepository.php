<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Repositories;

use App\Modules\Common\Application\Exceptions\ApiException;
use App\Modules\Common\Infrastructure\Repositories\BaseEloquentRepository;
use App\Modules\UserManagement\Domain\Entities\Role;
use App\Modules\UserManagement\Domain\Repositories\RoleRepositoryInterface;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Role\ListRolesRequest;
use Spatie\QueryBuilder\QueryBuilder;

final class EloquentRoleRepository extends BaseEloquentRepository implements RoleRepositoryInterface
{
    protected string $model = Role::class;

    protected string $requestClass = ListRolesRequest::class;

    public function find(int $id): Role
    {
        return QueryBuilder::for($this->model)
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->requestClass::getAllowedRelations())
            ->findOrFail($id);
    }

    public function findTrashed(int $id): ?Role
    {
        return QueryBuilder::for($this->model)
            ->onlyTrashed()
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->requestClass::getAllowedRelations())
            ->find($id);
    }

    public function create(array $data): Role
    {
        return Role::create(attributes: $data);
    }

    public function update(int $id, array $data): Role
    {
        $role = $this->find($id);

        $role->update(attributes: $data);

        return $role;
    }

    public function delete(int $id): void
    {
        $role = $this->find($id);

        $this->guardAgainstRelatedRecords($role);

        $role->delete();
    }

    public function forceDelete(int $id): void
    {
        $role = Role::withTrashed()->findOrFail($id);

        $this->guardAgainstRelatedRecords($role);

        $role->permissions()->detach();

        $role->forceDelete();
    }

    public function restore(int $id): Role
    {
        $role = Role::withTrashed()->findOrFail($id);

        $role->restore();

        return $role;
    }

    public function assignPermissions(int $roleId, array $permissions): Role
    {
        $role = $this->find($roleId);

        $role->syncPermissions(permissions: $permissions);

        return $role;
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

    private function guardAgainstRelatedRecords(Role $role): void
    {
        if (method_exists($role, 'hasRelatedRecords') && $role->hasRelatedRecords()) {
            throw new ApiException(
                errorCode: 'ROLE_IN_USE',
                message: 'Cannot delete role because it has related records.',
                httpCode: 409
            );
        }
    }
}
