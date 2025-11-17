<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Services;

use App\Models\User;
use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\Common\Application\UseCases\DeleteFileUseCase;
use App\Modules\UserManagement\Application\DTOs\UserData;
use App\Modules\UserManagement\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DeleteFileUseCase $deleteFileUseCase,
    ) {}

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
        return tap($this->userRepository->restore($id), fn () => $this->userRepository->invalidateCache());
    }

    public function createUser(UserData $userData): User
    {
        return tap(
            DB::transaction(function () use ($userData): User {
                $data = $userData->toArray();
                $data['createdBy'] = Auth::id();
                $roles = $data['roles'] ?? [];
                $permissions = $data['permissions'] ?? [];
                unset($data['roles'], $data['permissions']);

                $user = $this->userRepository->create($data);
                $this->syncRolesAndPermissions($user, $roles, $permissions);

                return $user;
            }),
            fn () => $this->userRepository->invalidateCache()
        );
    }

    public function updateUser(int $id, UserData $userData): User
    {
        return tap(
            DB::transaction(function () use ($id, $userData): User {
                $data = $userData->toArray();
                $data['updatedBy'] = Auth::id();
                $roles = $data['roles'] ?? [];
                $permissions = $data['permissions'] ?? [];
                unset($data['roles'], $data['permissions']);

                if (array_key_exists('password', $data) && $data['password'] === null) {
                    unset($data['password']);
                }

                $user = $this->userRepository->update($id, $data);
                $this->syncRolesAndPermissions($user, $roles, $permissions);

                return $user;
            }),
            fn () => $this->userRepository->invalidateCache()
        );
    }

    public function deleteUser(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $user = $this->userRepository->find($id);
            $this->deleteUserInstance($user, soft: true);
        });

        $this->userRepository->invalidateCache();
    }

    public function forceDeleteUser(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $user = $this->userRepository->findTrashed($id) ?? $this->userRepository->find($id);
            $this->deleteUserInstance($user, soft: false);
        });

        $this->userRepository->invalidateCache();
    }

    public function bulkDeleteUsers(array $ids): array
    {
        $deleted = [];
        $skipped = [];

        DB::transaction(function () use ($ids, &$deleted, &$skipped): void {
            foreach ($ids as $id) {
                $user = $this->userRepository->find($id);

                if (method_exists($user, 'hasRelatedRecords') && $user->hasRelatedRecords()) {
                    $skipped[] = $id;

                    continue;
                }

                $this->deleteUserInstance($user, soft: true);
                $deleted[] = $id;
            }
        });

        $this->userRepository->invalidateCache();

        return ['deleted' => $deleted, 'skipped' => $skipped];
    }

    public function updateUserLocale(User $user, string $locale): void
    {
        $this->userRepository->updateLocale($user, $locale);
    }

    private function deleteUserInstance(User $user, bool $soft): void
    {
        if ($soft) {
            $this->userRepository->delete($user->id);
        } else {
            $user->roles()->detach();
            $user->permissions()->detach();
            $this->deleteFileIfExists($user, 'profile_photo_path');
            $this->deleteFileIfExists($user, 'user_form_path');
            $this->userRepository->forceDelete($user->id);
        }
    }

    private function deleteFileIfExists(User $user, string $column): void
    {
        if ($user->$column) {
            $this->deleteFileUseCase->execute($user->$column);
        }
    }

    private function syncRolesAndPermissions(User $user, array $roles, array $permissions): void
    {
        if ($roles !== []) {
            $user->syncRoles($roles);
        }

        if ($permissions !== []) {
            $user->syncPermissions($permissions);
        }
    }
}
