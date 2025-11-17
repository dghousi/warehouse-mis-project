<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Role;

use App\Modules\UserManagement\Domain\Entities\Role;
use App\Modules\UserManagement\Domain\Services\RoleService;

final readonly class RestoreRoleUseCase
{
    public function __construct(private RoleService $roleService) {}

    public function execute(int $id): Role
    {
        return $this->roleService->restoreRole($id);
    }
}
