<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Role;

use App\Modules\UserManagement\Domain\Services\RoleService;

final readonly class BulkDeleteRolesUseCase
{
    public function __construct(private RoleService $roleService) {}

    public function execute(array $ids): array
    {
        return $this->roleService->bulkDeleteRoles($ids);
    }
}
