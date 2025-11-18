<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Permission;

use App\Modules\UserManagement\Domain\Services\PermissionService;

final readonly class DeletePermissionUseCase
{
    public function __construct(private PermissionService $permissionService) {}

    public function execute(int $id): void
    {
        $this->permissionService->deletePermission(id: $id);
    }
}
