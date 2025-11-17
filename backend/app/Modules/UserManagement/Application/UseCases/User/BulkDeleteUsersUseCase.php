<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Modules\UserManagement\Domain\Services\UserService;

final readonly class BulkDeleteUsersUseCase
{
    public function __construct(private UserService $userService) {}

    public function execute(array $ids): array
    {
        return $this->userService->bulkDeleteUsers($ids);
    }
}
