<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\User;

use App\Modules\Common\Infrastructure\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends BaseFormRequest
{
    private const NULLABLE_STRING = 'nullable|string|max:255';

    private const REQUIRED_STRING = 'required|string|max:255';

    private const NULLABLE_INTEGER = 'nullable|integer';

    protected array $booleanFields = ['notifications', 'enabled'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'firstName' => self::REQUIRED_STRING,
            'lastName' => self::NULLABLE_STRING,
            'profilePhotoPath' => [
                'sometimes',
                'nullable',
                'file',
                'max:5120', // 5MB
                'mimes:jpg,jpeg,png',
            ],
            'jobTitle' => self::REQUIRED_STRING,
            'reportToId' => self::NULLABLE_INTEGER,
            'email' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'contactNumber' => self::NULLABLE_STRING,
            'whatsappNumber' => self::NULLABLE_STRING,

            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
            ],

            'locale' => 'nullable|string|in:en,dr,ps',
            'mainOrganizationId' => self::NULLABLE_INTEGER,
            'rights' => 'nullable|string|in:create,review,approval',
            'notifications' => 'nullable|boolean',
            'enabled' => 'nullable|boolean',
            'status' => 'nullable|string|in:pending,approved,rejected,uploadForm',
            'remarks' => self::NULLABLE_STRING,
            'lastLoginAt' => 'nullable|date',
            'userFormPath' => [
                'sometimes',
                'nullable',
                'file',
                'max:5120', // 5MB
                'mimes:pdf,doc,docx,xls,xlsx',
            ],
            'createdBy' => self::NULLABLE_INTEGER,
            'updatedBy' => self::NULLABLE_INTEGER,
        ];
    }
}
