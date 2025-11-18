<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\Role;

use App\Modules\Common\Infrastructure\Http\Requests\BulkDeleteRequest;

final class BulkDeleteRolesRequest extends BulkDeleteRequest
{
    protected function tableName(): string
    {
        return 'roles';
    }

    protected function entityName(): string
    {
        return 'roles';
    }

    public function authorize(): bool
    {
        return true;
    }
}
