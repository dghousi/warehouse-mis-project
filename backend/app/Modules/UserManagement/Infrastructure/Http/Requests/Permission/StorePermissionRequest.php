<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\Permission;

use App\Modules\Common\Infrastructure\Http\Requests\BaseFormRequest;

final class StorePermissionRequest extends BaseFormRequest
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
            'name' => 'required|string|unique:permissions,name|max:255',
            'displayNameEn' => self::NULLABLE_STRING,
            'displayNamePs' => self::NULLABLE_STRING,
            'displayNameDr' => self::NULLABLE_STRING,
            'guardName' => 'string',
        ];
    }
}
