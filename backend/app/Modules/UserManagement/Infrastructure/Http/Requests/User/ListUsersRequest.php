<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\User;

use App\Modules\Common\Infrastructure\Http\Requests\BaseListRequest;

final class ListUsersRequest extends BaseListRequest
{
    protected static function sortableColumns(): array
    {
        return [
            'id',
            'first_name',
            'email',
            'created_at',
        ];
    }

    protected static function allowedRelations(): array
    {
        return [
            'reportTo',
            'createdBy',
            'updatedBy',
            'deletedBy',
            'user',
        ];
    }

    protected static function filters(): array
    {
        return [
            'main_organization_id' => [],
            'rights' => [
                'create',
                'review',
                'approval',
            ],
            'status' => [
                'pending',
                'approved',
                'rejected',
                'uploadForm',
            ],
            'enabled' => [
                0,
                1,
            ],
            'notifications' => [
                0,
                1,
            ],
        ];
    }

    protected static function searchableColumns(): array
    {
        return [
            'firstName' => 'first_name',
            'lastName' => 'last_name',
            'email' => 'email',
            'jobTitle' => 'job_title',
            'contactNumber' => 'contact_number',
            'whatsappNumber' => 'whatsapp_number',
            'remarks' => 'remarks',
        ];
    }

    protected static function fieldableColumns(): array
    {
        return [
            'id' => 'id',
            'firstName' => 'first_name',
            'lastName' => 'last_name',
            'profilePhotoPath' => 'profile_photo_path',
            'jobTitle' => 'job_title',
            'reportToId' => 'report_to_id',
            'email' => 'email',
            'emailVerifiedAt' => 'email_verified_at',
            'contactNumber' => 'contact_number',
            'whatsappNumber' => 'whatsapp_number',
            'locale' => 'locale',
            'mainOrganizationId' => 'main_organization_id',
            'rights' => 'rights',
            'notifications' => 'notifications',
            'enabled' => 'enabled',
            'status' => 'status',
            'remarks' => 'remarks',
            'lastLoginAt' => 'last_login_at',
            'userFormPath' => 'user_form_path',
            'createdBy' => 'created_by',
            'updatedBy' => 'updated_by',
            'deletedBy' => 'deleted_by',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
            'deletedAt' => 'deleted_at',
        ];
    }
}
