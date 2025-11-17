<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Repositories;

use App\Models\User;
use App\Modules\Common\Application\DTOs\QuerySpecification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(QuerySpecification $querySpecification): LengthAwarePaginator;

    public function find(int $id): User;

    public function findTrashed(int $id): ?User;

    public function create(array $data): User;

    public function update(int $id, array $data): User;

    public function delete(int $id): void;

    public function forceDelete(int $id): void;

    public function restore(int $id): User;

    public function updateLocale(User $user, string $locale): void;

    public function invalidateCache(): void;
}
