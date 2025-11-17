<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Permission;

use App\Modules\UserManagement\Domain\Entities\Permission;
use App\Modules\UserManagement\Domain\Services\PermissionService;

final readonly class GetPermissionUseCase
{
    public function __construct(private PermissionService $permissionService) {}

    public function execute(int $id): Permission
    {
        return $this->permissionService->getPermission(id: $id);
    }
}
