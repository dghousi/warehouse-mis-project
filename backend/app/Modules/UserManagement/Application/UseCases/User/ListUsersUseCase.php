<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\UserManagement\Domain\Services\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListUsersUseCase
{
    public function __construct(private UserService $userService) {}

    public function execute(QuerySpecification $querySpecification): LengthAwarePaginator
    {
        return $this->userService->listUsers($querySpecification);
    }
}
