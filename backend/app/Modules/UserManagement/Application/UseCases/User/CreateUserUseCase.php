<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Modules\UserManagement\Application\DTOs\UserData;
use App\Modules\UserManagement\Domain\Entities\User;
use App\Modules\UserManagement\Domain\Services\UserService;

final readonly class CreateUserUseCase
{
    public function __construct(
        private UserService $userService
    ) {}

    public function execute(UserData $userData): User
    {
        return $this->userService->createUser(data: $userData->toArray());
    }
}
