<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Controllers;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\Common\Infrastructure\Http\Controllers\BaseApiController;
use App\Modules\Common\Infrastructure\Resources\PaginatedResource;
use App\Modules\UserManagement\Application\DTOs\RoleData;
use App\Modules\UserManagement\Application\UseCases\Role\AssignPermissionsToRoleUseCase;
use App\Modules\UserManagement\Application\UseCases\Role\BulkDeleteRolesUseCase;
use App\Modules\UserManagement\Application\UseCases\Role\CreateRoleUseCase;
use App\Modules\UserManagement\Application\UseCases\Role\DeleteRoleUseCase;
use App\Modules\UserManagement\Application\UseCases\Role\ForceDeleteRoleUseCase;
use App\Modules\UserManagement\Application\UseCases\Role\GetRoleUseCase;
use App\Modules\UserManagement\Application\UseCases\Role\ListRolesUseCase;
use App\Modules\UserManagement\Application\UseCases\Role\RestoreRoleUseCase;
use App\Modules\UserManagement\Application\UseCases\Role\UpdateRoleUseCase;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Role\BulkDeleteRolesRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Role\ListRolesRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Role\StoreRoleRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Role\UpdateRoleRequest;
use App\Modules\UserManagement\Infrastructure\Resources\RoleResource;
use Illuminate\Http\JsonResponse;

final class RoleController extends BaseApiController
{
    public function __construct(
        private readonly ListRolesUseCase $listRolesUseCase,
        private readonly GetRoleUseCase $getRoleUseCase,
        private readonly RestoreRoleUseCase $restoreRoleUseCase,
        private readonly CreateRoleUseCase $createRoleUseCase,
        private readonly UpdateRoleUseCase $updateRoleUseCase,
        private readonly DeleteRoleUseCase $deleteRoleUseCase,
        private readonly ForceDeleteRoleUseCase $forceDeleteRoleUseCase,
        private readonly BulkDeleteRolesUseCase $bulkDeleteRolesUseCase,
        private readonly AssignPermissionsToRoleUseCase $assignPermissionsToRoleUseCase
    ) {}

    public function index(ListRolesRequest $listRolesRequest): JsonResponse
    {
        $querySpecification = QuerySpecification::fromArray($listRolesRequest->validated());

        $lengthAwarePaginator = $this->listRolesUseCase->execute($querySpecification);

        $paginatedResource = new PaginatedResource($lengthAwarePaginator, RoleResource::class);

        return $this->successResponse(
            data: $paginatedResource,
            message: __(key: 'UserManagement::messages.roles.fetched')
        );
    }

    public function store(StoreRoleRequest $storeRoleRequest): JsonResponse
    {
        $roleData = RoleData::fromArray($storeRoleRequest->validated());
        $role = $this->createRoleUseCase->execute(roleData: $roleData);

        return $this->successResponse(
            data: new RoleResource(resource: $role),
            message: __('UserManagement::messages.role.created'),
            code: 201
        );
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->getRoleUseCase->execute($id);

        return $this->successResponse(
            data: new RoleResource(resource: $role),
            message: __(key: 'UserManagement::messages.role.fetched')
        );
    }

    public function restore(int $id): JsonResponse
    {
        $role = $this->restoreRoleUseCase->execute($id);

        return $this->successResponse(
            data: new RoleResource($role),
            message: __(key: 'UserManagement::messages.role.restored')
        );
    }

    public function update(UpdateRoleRequest $updateRoleRequest, int $id): JsonResponse
    {
        $roleData = RoleData::fromArray($updateRoleRequest->validated());
        $role = $this->updateRoleUseCase->execute(id: $id, roleData: $roleData);

        return $this->successResponse(
            data: new RoleResource(resource: $role),
            message: __(key: 'UserManagement::messages.role.updated')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteRoleUseCase->execute(id: $id);

        return $this->successResponse(
            data: null,
            message: __(key: 'UserManagement::messages.role.deleted')
        );
    }

    public function forceDelete(int $id): JsonResponse
    {
        $this->forceDeleteRoleUseCase->execute($id);

        return $this->successResponse(
            data: null,
            message: __('UserManagement::messages.role.force_deleted')
        );
    }

    public function bulkDelete(BulkDeleteRolesRequest $bulkDeleteRolesRequest): JsonResponse
    {
        $result = $this->bulkDeleteRolesUseCase->execute($bulkDeleteRolesRequest->validated('ids'));

        $message = __('UserManagement::messages.role.bulk_deleted');
        if ($result['skipped']) {
            $message .= ' '.__('UserManagement::messages.role.bulk_skipped');
        }

        return $this->successResponse(
            data: $result,
            message: $message
        );
    }

    public function assignPermissions(int $roleId, StoreRoleRequest $storeRoleRequest): JsonResponse
    {
        $permissions = $storeRoleRequest->input(key: 'permissions', default: []);

        $role = $this->assignPermissionsToRoleUseCase->execute(roleId: $roleId, permissionIds: $permissions);

        return $this->successResponse(
            data: new RoleResource(resource: $role),
            message: __('UserManagement::messages.role.assigned')
        );
    }
}
