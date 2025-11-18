<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\User;

use App\Modules\Common\Infrastructure\Http\Requests\BulkDeleteRequest;

final class BulkDeleteUsersRequest extends BulkDeleteRequest
{
    protected function tableName(): string
    {
        return 'users';
    }

    protected function entityName(): string
    {
        return 'User';
    }

    public function authorize(): bool
    {
        return true;
    }
}
