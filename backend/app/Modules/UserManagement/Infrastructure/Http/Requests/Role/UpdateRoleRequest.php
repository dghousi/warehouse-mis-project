<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\Role;

use App\Modules\Common\Infrastructure\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoleRequest extends BaseFormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(table: 'roles', column: 'name')->ignore(id: $this->route(param: 'role')),
            ],
            'displayNameEn' => self::NULLABLE_STRING,
            'displayNamePs' => self::NULLABLE_STRING,
            'displayNameDr' => self::NULLABLE_STRING,
            'guardName' => 'string',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ];
    }
}
