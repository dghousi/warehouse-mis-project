<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Permission;

use App\Modules\UserManagement\Application\DTOs\PermissionData;
use App\Modules\UserManagement\Domain\Entities\Permission;
use App\Modules\UserManagement\Domain\Services\PermissionService;

final readonly class UpdatePermissionUseCase
{
    public function __construct(private PermissionService $permissionService) {}

    public function execute(int $id, PermissionData $permissionData): Permission
    {
        return $this->permissionService->updatePermission(id: $id, data: $permissionData->toArray());
    }
}
