<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Http\Requests\User;

use App\Modules\Common\Infrastructure\Http\Requests\BaseListRequest;

final class ListUsersRequest extends BaseListRequest
{
    protected static function sortableColumns(): array
    {
        return [
            'id' => 'id',
            'firstName' => 'first_name',
            'lastName' => 'last_name',
            'email' => 'email',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
            'jobTitle' => 'job_title',
            'enabled' => 'enabled',
            'status' => 'status',
        ];
    }

    protected static function allowedRelations(): array
    {
        return ['roles', 'reportTo'];
    }

    protected static function filters(): array
    {
        return [
            'status' => ['pending', 'approved', 'rejected', 'uploadForm'],
            'rights' => ['create', 'review', 'approval'],
            'enabled' => [0, 1],
        ];
    }

    public static function searchableColumns(): array
    {
        return [
            'firstName' => 'first_name',
            'lastName' => 'last_name',
            'email' => 'email',
            'jobTitle' => 'job_title',
        ];
    }

    protected static function fieldableColumns(): array
    {
        return [
            'id' => 'id',
            'firstName' => 'first_name',
            'lastName' => 'last_name',
            'email' => 'email',
            'reportToId' => 'report_to_id',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
            'jobTitle' => 'job_title',
            'enabled' => 'enabled',
            'status' => 'status',
        ];
    }
}
