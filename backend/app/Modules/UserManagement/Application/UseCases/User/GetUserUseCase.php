<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Modules\UserManagement\Domain\Entities\User;
use App\Modules\UserManagement\Domain\Services\UserService;

final readonly class GetUserUseCase
{
    public function __construct(private UserService $userService) {}

    public function execute(int $id): User
    {
        return $this->userService->getUser(id: $id);
    }
}
