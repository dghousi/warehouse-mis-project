<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Controllers;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\Common\Infrastructure\Http\Controllers\BaseApiController;
use App\Modules\Common\Infrastructure\Resources\PaginatedResource;
use App\Modules\UserManagement\Application\DTOs\UserData;
use App\Modules\UserManagement\Application\UseCases\User\BulkDeleteUsersUseCase;
use App\Modules\UserManagement\Application\UseCases\User\CreateUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\DeleteUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\ForceDeleteUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\GetUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\ListUsersUseCase;
use App\Modules\UserManagement\Application\UseCases\User\RestoreUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\UpdateUserUseCase;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\BulkDeleteUsersRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\ListUsersRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\StoreUserRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\UpdateUserRequest;
use App\Modules\UserManagement\Infrastructure\Resources\UserResource;
use Illuminate\Http\JsonResponse;

final class UserController extends BaseApiController
{
    public function __construct(
        private readonly ListUsersUseCase $listUsersUseCase,
        private readonly GetUserUseCase $getUserUseCase,
        private readonly RestoreUserUseCase $restoreUserUseCase,
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly DeleteUserUseCase $deleteUserUseCase,
        private readonly ForceDeleteUserUseCase $forceDeleteUserUseCase,
        private readonly BulkDeleteUsersUseCase $bulkDeleteUsersUseCase,
    ) {}

    public function index(ListUsersRequest $listUsersRequest): JsonResponse
    {
        $querySpecification = QuerySpecification::fromArray($listUsersRequest->validated());

        $lengthAwarePaginator = $this->listUsersUseCase->execute($querySpecification);

        $paginatedResource = new PaginatedResource($lengthAwarePaginator, UserResource::class);

        return $this->successResponse(
            data: $paginatedResource,
            message: __('UserManagement::messages.users.fetched')
        );
    }

    public function store(StoreUserRequest $storeUserRequest): JsonResponse
    {
        $userData = UserData::fromArray($storeUserRequest->validated());
        $user = $this->createUserUseCase->execute(userData: $userData);

        return $this->successResponse(
            data: new UserResource(resource: $user),
            message: __('UserManagement::messages.user.created'),
            code: 201
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->getUserUseCase->execute($id);

        return $this->successResponse(
            data: new UserResource(resource: $user),
            message: __('UserManagement::messages.user.fetched')
        );
    }

    public function restore(int $id): JsonResponse
    {
        $user = $this->restoreUserUseCase->execute($id);

        return $this->successResponse(
            data: new UserResource(resource: $user),
            message: __('UserManagement::messages.user.restored')
        );
    }

    public function update(UpdateUserRequest $updateUserRequest, int $id): JsonResponse
    {
        $userData = UserData::fromArray($updateUserRequest->validated());
        $user = $this->updateUserUseCase->execute(id: $id, userData: $userData);

        return $this->successResponse(
            data: new UserResource(resource: $user),
            message: __('UserManagement::messages.user.updated')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteUserUseCase->execute(id: $id);

        return $this->successResponse(
            data: null,
            message: __('UserManagement::messages.user.deleted')
        );
    }

    public function forceDelete(int $id): JsonResponse
    {
        $this->forceDeleteUserUseCase->execute($id);

        return $this->successResponse(
            data: null,
            message: __('UserManagement::messages.user.force_deleted')
        );
    }

    public function bulkDelete(BulkDeleteUsersRequest $bulkDeleteUsersRequest): JsonResponse
    {
        $result = $this->bulkDeleteUsersUseCase->execute($bulkDeleteUsersRequest->validated('ids'));

        $message = __('UserManagement::messages.users.bulk_deleted');
        if ($result['skipped']) {
            $message .= ' '.__('UserManagement::messages.users.bulk_skipped');
        }

        return $this->successResponse(
            data: $result,
            message: $message
        );
    }
}
