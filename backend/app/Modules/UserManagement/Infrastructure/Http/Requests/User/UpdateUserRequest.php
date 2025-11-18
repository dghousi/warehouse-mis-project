<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\User;

use App\Modules\Common\Infrastructure\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends BaseFormRequest
{
    protected array $booleanFields = ['notifications', 'enabled'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName' => 'required|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'profilePhotoPath' => 'nullable|string|max:255',
            'jobTitle' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'max:255',
                Rule::unique(table: 'users', column: 'email')->ignore(id: $this->route(param: 'user')),
            ],
            'emailVerifiedAt' => 'nullable|date',
            'contactNumber' => 'nullable|string|max:255',
            'whatsappNumber' => 'nullable|string|max:255',
            'password' => 'required|string|max:255',
            'locale' => 'nullable|nullable|string|in:en,dr,ps',
            'rights' => 'nullable|nullable|string|in:create,review,approval',
            'notifications' => 'nullable|boolean',
            'enabled' => 'nullable|boolean',
            'status' => 'nullable|nullable|string|in:pending,approved,rejected,uploadForm',
            'remarks' => 'nullable|string|max:255',
            'lastLoginAt' => 'nullable|date',
            'userFormPath' => 'nullable|string|max:255',
            'reportToId' => 'nullable|integer|exists:report_tos,id',
            'createdBy' => 'nullable|integer|exists:created_bies,id',
            'updatedBy' => 'nullable|integer|exists:updated_bies,id',
            'deletedBy' => 'nullable|integer|exists:deleted_bies,id',
            'token' => 'required|string|max:255',
            'createdAt' => 'nullable|date',
            'id' => 'required|string|max:255',
            'userId' => 'nullable|integer|exists:users,id',
            'ipAddress' => 'nullable|string|max:255',
            'userAgent' => 'nullable|string|max:255',
            'payload' => 'required|string|max:255',
            'lastActivity' => 'required|integer',
        ];
    }
}
