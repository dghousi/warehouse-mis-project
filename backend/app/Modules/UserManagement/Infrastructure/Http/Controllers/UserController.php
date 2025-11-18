<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Controllers;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\Common\Infrastructure\Http\Controllers\BaseApiController;
use App\Modules\Common\Infrastructure\Resources\PaginatedResource;
use App\Modules\UserManagement\Application\UseCases\User\BulkDeleteUsersUseCase;
use App\Modules\UserManagement\Application\UseCases\User\CreateUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\DeleteUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\ForceDeleteUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\GetUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\ListUsersUseCase;
use App\Modules\UserManagement\Application\UseCases\User\RestoreUserUseCase;
use App\Modules\UserManagement\Application\UseCases\User\SetUserLocaleUseCase;
use App\Modules\UserManagement\Application\UseCases\User\UpdateUserUseCase;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\BulkDeleteUsersRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\ListUsersRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\SetUserLocaleRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\StoreUserRequest;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\UpdateUserRequest;
use App\Modules\UserManagement\Infrastructure\Resources\UserResource;
use Illuminate\Http\JsonResponse;

final class UserController extends BaseApiController
{
    private const FILE_FIELDS = ['profilePhotoPath', 'userFormPath'];

    public function __construct(
        private readonly ListUsersUseCase $listUsersUseCase,
        private readonly GetUserUseCase $getUserUseCase,
        private readonly RestoreUserUseCase $restoreUserUseCase,
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly DeleteUserUseCase $deleteUserUseCase,
        private readonly ForceDeleteUserUseCase $forceDeleteUserUseCase,
        private readonly BulkDeleteUsersUseCase $bulkDeleteUsersUseCase,
        private readonly SetUserLocaleUseCase $setUserLocaleUseCase,
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
        $input = $this->extractFiles($storeUserRequest, $storeUserRequest->validated());
        $user = $this->createUserUseCase->execute($input);

        return $this->successResponse(
            data: new UserResource($user),
            message: __('UserManagement::messages.user.created'),
            code: 201
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->getUserUseCase->execute($id);

        return $this->successResponse(
            data: new UserResource($user),
            message: __(key: 'UserManagement::messages.user.fetched')
        );
    }

    public function restore(int $id): JsonResponse
    {
        $user = $this->restoreUserUseCase->execute($id);

        return $this->successResponse(
            data: new UserResource($user),
            message: __(key: 'UserManagement::messages.user.restored')
        );
    }

    public function update(UpdateUserRequest $updateUserRequest, int $id): JsonResponse
    {
        $input = $this->extractFiles($updateUserRequest, $updateUserRequest->validated());
        $user = $this->updateUserUseCase->execute($id, $input);

        return $this->successResponse(
            data: new UserResource($user),
            message: __(key: 'UserManagement::messages.user.updated')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteUserUseCase->execute($id);

        return $this->successResponse(
            data: null,
            message: __(key: 'UserManagement::messages.user.deleted')
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

        $message = 'Bulk deletion completed.';
        if ($result['skipped']) {
            $message .= ' Some users were skipped due to related records.';
        }

        return $this->successResponse($result, $message);
    }

    public function setLocale(SetUserLocaleRequest $setUserLocaleRequest): JsonResponse
    {
        $user = $setUserLocaleRequest->user();

        $this->setUserLocaleUseCase->execute($user, $setUserLocaleRequest->validated('locale'));

        return $this->successResponse(
            data: null,
            message: __('UserManagement::messages.user.locale_updated')
        );
    }

    private function extractFiles(StoreUserRequest|UpdateUserRequest $req, array $data): array
    {
        foreach (self::FILE_FIELDS as $field) {
            if ($req->hasFile($field)) {
                $data[$field] = $req->file($field);
            } elseif ($req instanceof UpdateUserRequest && $req->has($field)) {
                $data[$field] = $req->input($field) ?? null;
            }
        }

        return $data;
    }
}
