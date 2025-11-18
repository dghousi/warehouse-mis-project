<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\Role;

use App\Modules\Common\Infrastructure\Http\Requests\BaseListRequest;

final class ListRolesRequest extends BaseListRequest
{
    protected static function sortableColumns(): array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'createdAt' => 'created_at',
        ];
    }

    protected static function allowedRelations(): array
    {
        return ['permissions'];
    }

    protected static function filters(): array
    {
        return [];
    }

    public static function searchableColumns(): array
    {
        return [
            'displayName' => ['display_name_en', 'display_name_ps', 'display_name_dr'],
        ];
    }

    protected static function fieldableColumns(): array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'displayNameEn' => 'display_name_en',
            'displayNamePs' => 'display_name_ps',
            'displayNameDr' => 'display_name_dr',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];
    }
}
