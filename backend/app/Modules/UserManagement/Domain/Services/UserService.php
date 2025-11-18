<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Services;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\UserManagement\Domain\Entities\User;
use App\Modules\UserManagement\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class UserService
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function listUsers(QuerySpecification $querySpecification): LengthAwarePaginator
    {
        return $this->userRepository->paginate($querySpecification);
    }

    public function getUser(int $id): User
    {
        return $this->userRepository->find(id: $id);
    }

    public function restoreUser(int $id): User
    {
        return tap(
            $this->userRepository->restore($id),
            fn () => $this->userRepository->invalidateCache()
        );
    }

    public function createUser(array $data): User
    {
        return tap(
            $this->userRepository->create(data: $data),
            fn () => $this->userRepository->invalidateCache()
        );
    }

    public function updateUser(int $id, array $data): User
    {
        return tap(
            $this->userRepository->update(id: $id, data: $data),
            fn () => $this->userRepository->invalidateCache()
        );
    }

    public function deleteUser(int $id): void
    {
        $this->userRepository->delete(id: $id);
        $this->userRepository->invalidateCache();
    }

    public function forceDeleteUser(int $id): void
    {
        $permission = $this->userRepository->findTrashed($id) ?? $this->userRepository->find($id);
        $this->userRepository->forceDelete($permission->id);
        $this->userRepository->invalidateCache();
    }

    public function bulkDeleteUsers(array $ids): array
    {
        $deleted = [];
        $skipped = [];

        foreach ($ids as $id) {
            $user = $this->userRepository->find($id);

            if (method_exists($user, 'hasRelatedRecords') && $user->hasRelatedRecords()) {
                $skipped[] = $id;

                continue;
            }

            $this->userRepository->delete(id: $id);
            $deleted[] = $id;
        }

        $this->userRepository->invalidateCache();

        return ['deleted' => $deleted, 'skipped' => $skipped];
    }
}
