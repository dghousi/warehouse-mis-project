<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\Permission;

use App\Modules\Common\Infrastructure\Http\Requests\BulkDeleteRequest;

final class BulkDeletePermissionsRequest extends BulkDeleteRequest
{
    protected function tableName(): string
    {
        return 'permissions';
    }

    protected function entityName(): string
    {
        return 'permissions';
    }

    public function authorize(): bool
    {
        return true;
    }
}
