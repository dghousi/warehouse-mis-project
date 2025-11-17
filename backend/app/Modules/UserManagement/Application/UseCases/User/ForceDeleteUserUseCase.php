<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Modules\UserManagement\Domain\Services\UserService;

final readonly class ForceDeleteUserUseCase
{
    public function __construct(private UserService $userService) {}

    public function execute(int $id): void
    {
        $this->userService->forceDeleteUser($id);
    }
}
