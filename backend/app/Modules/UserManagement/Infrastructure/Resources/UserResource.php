<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->filterEmpty([
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'profilePhotoPath' => $this->profile_photo_path,
            'jobTitle' => $this->job_title,
            'email' => $this->email,
            'emailVerifiedAt' => $this->email_verified_at,
            'contactNumber' => $this->contact_number,
            'whatsappNumber' => $this->whatsapp_number,
            'password' => $this->password,
            'locale' => $this->locale,
            'rights' => $this->rights,
            'notifications' => $this->notifications,
            'enabled' => $this->enabled,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'lastLoginAt' => $this->last_login_at,
            'userFormPath' => $this->user_form_path,
            'token' => $this->token,
            'createdAt' => $this->created_at,
            'ipAddress' => $this->ip_address,
            'userAgent' => $this->user_agent,
            'payload' => $this->payload,
            'lastActivity' => $this->last_activity,
            'reportTo' => $this->reportTo,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
            'deletedBy' => $this->deletedBy,
            'user' => $this->user,
            'users' => $this->users,
            'activeUsers' => $this->activeUsers,
            'fileUploads' => $this->fileUploads,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ]);
    }

    private function filterEmpty(array $data): array
    {
        return array_filter($data, fn ($value): bool => !in_array($value, [null, '', []], true) && !($value instanceof Collection && $value->isEmpty()));
    }
}
