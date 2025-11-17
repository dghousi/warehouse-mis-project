<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Role;

use App\Modules\UserManagement\Domain\Entities\Role;
use App\Modules\UserManagement\Domain\Services\RoleService;

final readonly class AssignPermissionsToRoleUseCase
{
    public function __construct(private RoleService $roleService) {}

    public function execute(int $roleId, array $permissionIds): Role
    {
        return $this->roleService->assignPermissions(roleId: $roleId, permissionIds: $permissionIds);
    }
}
