<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Role;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\UserManagement\Domain\Services\RoleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListRolesUseCase
{
    public function __construct(private RoleService $roleService) {}

    public function execute(QuerySpecification $querySpecification): LengthAwarePaginator
    {
        return $this->roleService->listRoles($querySpecification);
    }
}
