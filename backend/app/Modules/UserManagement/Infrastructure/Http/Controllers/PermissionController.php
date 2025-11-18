<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Controllers;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\Common\Infrastructure\Http\Controllers\BaseApiController;
use App\Modules\Common\Infrastructure\Resources\PaginatedResource;
use App\Modules\UserManagement\Application\DTOs\PermissionData;
use App\Modules\UserManagement\Application\UseCases\Permission\BulkDeletePermissionsUseCase;
use App\Modules\UserManagement\Application\UseCases\Permission\CreatePermissionUseCase;
use App\Modules\UserManagement\Application\UseCases\Permission\DeletePermissionUseCase;
use App\Modules\UserManagement\Application\UseCases\Permission\ForceDeletePermissionUseCase;
use App\Modules\UserManagement\Application\UseCases\Permission\GetPermissionUseCase;
use App\Modules\UserManagement\Application\UseCases\Permission\ListPermissionsUseCase;
use App\Modules\UserManagement\Application\UseCases\Permission\RestorePermissionUseCase;
use App\Modules\UserManagement\Application\UseCases\Permission\UpdatePermissionUseCase;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Permission\BulkDeletePermissionsRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Permission\ListPermissionsRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Permission\StorePermissionRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\Permission\UpdatePermissionRequest;
use App\Modules\UserManagement\Infrastructure\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;

final class PermissionController extends BaseApiController
{
    public function __construct(
        private readonly ListPermissionsUseCase $listPermissionsUseCase,
        private readonly GetPermissionUseCase $getPermissionUseCase,
        private readonly RestorePermissionUseCase $restorePermissionUseCase,
        private readonly CreatePermissionUseCase $createPermissionUseCase,
        private readonly UpdatePermissionUseCase $updatePermissionUseCase,
        private readonly DeletePermissionUseCase $deletePermissionUseCase,
        private readonly ForceDeletePermissionUseCase $forceDeletePermissionUseCase,
        private readonly BulkDeletePermissionsUseCase $bulkDeletePermissionsUseCase,
    ) {}

    public function index(ListPermissionsRequest $listPermissionsRequest): JsonResponse
    {
        $querySpecification = QuerySpecification::fromArray($listPermissionsRequest->validated());

        $lengthAwarePaginator = $this->listPermissionsUseCase->execute($querySpecification);

        $paginatedResource = new PaginatedResource($lengthAwarePaginator, PermissionResource::class);

        return $this->successResponse(
            data: $paginatedResource,
            message: __('UserManagement::messages.permissions.fetched')
        );
    }

    public function store(StorePermissionRequest $storePermissionRequest): JsonResponse
    {
        $permissionData = PermissionData::fromArray($storePermissionRequest->validated());
        $permission = $this->createPermissionUseCase->execute(permissionData: $permissionData);

        return $this->successResponse(
            data: new PermissionResource(resource: $permission),
            message: __('UserManagement::messages.permission.created'),
            code: 201
        );
    }

    public function show(int $id): JsonResponse
    {
        $permission = $this->getPermissionUseCase->execute($id);

        return $this->successResponse(
            data: new PermissionResource(resource: $permission),
            message: __('UserManagement::messages.permission.fetched')
        );
    }

    public function restore(int $id): JsonResponse
    {
        $permission = $this->restorePermissionUseCase->execute($id);

        return $this->successResponse(
            data: new PermissionResource(resource: $permission),
            message: __('UserManagement::messages.permission.restored')
        );
    }

    public function update(UpdatePermissionRequest $updatePermissionRequest, int $id): JsonResponse
    {
        $permissionData = PermissionData::fromArray($updatePermissionRequest->validated());
        $permission = $this->updatePermissionUseCase->execute(id: $id, permissionData: $permissionData);

        return $this->successResponse(
            data: new PermissionResource(resource: $permission),
            message: __('UserManagement::messages.permission.updated')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deletePermissionUseCase->execute(id: $id);

        return $this->successResponse(
            data: null,
            message: __('UserManagement::messages.permission.deleted')
        );
    }

    public function forceDelete(int $id): JsonResponse
    {
        $this->forceDeletePermissionUseCase->execute($id);

        return $this->successResponse(
            data: null,
            message: __('UserManagement::messages.permission.force_deleted')
        );
    }

    public function bulkDelete(BulkDeletePermissionsRequest $bulkDeletePermissionsRequest): JsonResponse
    {
        $result = $this->bulkDeletePermissionsUseCase->execute($bulkDeletePermissionsRequest->validated('ids'));

        $message = __('UserManagement::messages.permission.bulk_deleted');
        if ($result['skipped']) {
            $message .= ' '.__('UserManagement::messages.permission.bulk_skipped');
        }

        return $this->successResponse(
            data: $result,
            message: $message
        );
    }
}
