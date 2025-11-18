<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Role;

use App\Modules\UserManagement\Application\DTOs\RoleData;
use App\Modules\UserManagement\Domain\Entities\Role;
use App\Modules\UserManagement\Domain\Services\RoleService;

final readonly class CreateRoleUseCase
{
    public function __construct(private RoleService $roleService) {}

    public function execute(RoleData $roleData): Role
    {
        return $this->roleService->createRole(data: $roleData->toArray());
    }
}
