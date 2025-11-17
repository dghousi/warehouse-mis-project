<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\Role;

use App\Modules\Common\Infrastructure\Http\Requests\BaseFormRequest;

final class StoreRoleRequest extends BaseFormRequest
{
    private const NULLABLE_STRING = 'nullable|string';

    protected array $booleanFields = [];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
            'name' => 'required|string|unique:roles,name|max:255',
            'displayNameEn' => self::NULLABLE_STRING,
            'displayNamePs' => self::NULLABLE_STRING,
            'displayNameDr' => self::NULLABLE_STRING,
            'guardName' => 'string',
        ];

    }
}
