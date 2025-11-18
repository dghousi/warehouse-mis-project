<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Modules\UserManagement\Application\DTOs\UserData;
use App\Modules\UserManagement\Domain\Entities\User;
use App\Modules\UserManagement\Domain\Services\UserService;

final readonly class UpdateUserUseCase
{
    public function __construct(private UserService $userService) {}

    public function execute(int $id, UserData $userData): User
    {
        return $this->userService->updateUser(id: $id, data: $userData->toArray());
    }
}
