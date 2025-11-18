<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Repositories;

use App\Models\User;
use App\Modules\Common\Application\Exceptions\ApiException;
use App\Modules\Common\Infrastructure\Repositories\BaseEloquentRepository;
use App\Modules\UserManagement\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManagement\Infrastructure\Http\Requests\User\ListUsersRequest;
use Spatie\QueryBuilder\QueryBuilder;

final class EloquentUserRepository extends BaseEloquentRepository implements UserRepositoryInterface
{
    protected string $model = User::class;

    protected string $requestClass = ListUsersRequest::class;

    public function find(int $id): User
    {
        return QueryBuilder::for($this->model)
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->requestClass::getAllowedRelations())
            ->findOrFail($id);
    }

    public function findTrashed(int $id): ?User
    {
        return QueryBuilder::for($this->model)
            ->onlyTrashed()
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->requestClass::getAllowedRelations())
            ->find($id);
    }

    public function create(array $data): User
    {
        return User::create(attributes: $data);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->find($id);

        $user->update($data);

        return $user;
    }

    public function delete(int $id): void
    {
        $user = $this->find(id: $id);

        $this->guardAgainstRelatedRecords($user);

        $user->delete();
    }

    public function forceDelete(int $id): void
    {
        $user = User::withTrashed()->findOrFail($id);

        $this->guardAgainstRelatedRecords($user);

        $user->forceDelete();
    }

    public function restore(int $id): User
    {
        $user = User::withTrashed()->findOrFail($id);

        $user->restore();

        return $user;
    }

    public function updateLocale(User $user, string $locale): void
    {
        $user->update(['locale' => $locale]);
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

    private function guardAgainstRelatedRecords(User $user): void
    {
        if (method_exists($user, 'hasRelatedRecords') && $user->hasRelatedRecords()) {
            throw new ApiException(
                errorCode: 'USER_IN_USE',
                message: 'Cannot delete user because it has related records.',
                httpCode: 409
            );
        }
    }
}
