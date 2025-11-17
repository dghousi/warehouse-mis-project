<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\Permission;

use App\Modules\Common\Application\DTOs\QuerySpecification;
use App\Modules\UserManagement\Domain\Services\PermissionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListPermissionsUseCase
{
    public function __construct(private PermissionService $permissionService) {}

    public function execute(QuerySpecification $querySpecification): LengthAwarePaginator
    {
        return $this->permissionService->listPermissions($querySpecification);
    }
}
