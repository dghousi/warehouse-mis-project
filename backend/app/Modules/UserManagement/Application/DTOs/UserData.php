<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\DTOs;

use App\Modules\Common\Infrastructure\Traits\IdCaster;

final readonly class UserData
{
    use IdCaster;

    public function __construct(
        public string $firstName,
        public ?string $lastName,
        public ?string $profilePhotoPath,
        public string $jobTitle,
        public string $email,
        public ?string $emailVerifiedAt,
        public ?string $contactNumber,
        public ?string $whatsappNumber,
        public string $password,
        public ?string $remarks,
        public ?string $lastLoginAt,
        public ?string $userFormPath,
        public ?int $createdBy,
        public ?int $updatedBy,
        public ?int $deletedBy,
        public string $token,
        public ?string $createdAt,
        public string $id,
        public ?int $userId,
        public string $ipAddress,
        public ?string $userAgent,
        public string $payload,
        public int $lastActivity,
        public string $locale = 'en',
        public string $rights = 'review',
        public bool $notifications = true,
        public bool $enabled = true,
        public string $status = 'pending',
        public int $reportToId = 0,
        public array $users = [],
        public array $activeUsers = [],
        public array $fileUploads = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $ids = self::castIds(['reportToId', 'mainOrganizationId', 'createdBy', 'updatedBy', 'deletedBy'], $data);

        return new self(
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            profilePhotoPath: $data['profilePhotoPath'] ?? null,
            jobTitle: $data['jobTitle'] ?? null,
            email: $data['email'] ?? null,
            emailVerifiedAt: $data['emailVerifiedAt'] ?? null,
            contactNumber: $data['contactNumber'] ?? null,
            whatsappNumber: $data['whatsappNumber'] ?? null,
            password: $data['password'] ?? null,
            remarks: $data['remarks'] ?? null,
            lastLoginAt: $data['lastLoginAt'] ?? null,
            userFormPath: $data['userFormPath'] ?? null,
            createdBy: $ids['createdBy'] ?? null,
            updatedBy: $ids['updatedBy'] ?? null,
            deletedBy: $ids['deletedBy'] ?? null,
            token: $data['token'] ?? null,
            createdAt: $data['createdAt'] ?? null,
            id: $data['id'] ?? null,
            userId: $data['userId'] ?? null,
            ipAddress: $data['ipAddress'] ?? null,
            userAgent: $data['userAgent'] ?? null,
            payload: $data['payload'] ?? null,
            lastActivity: $data['lastActivity'] ?? null,
            locale: $data['locale'] ?? 'en',
            rights: $data['rights'] ?? 'review',
            notifications: $data['notifications'] ?? true,
            enabled: $data['enabled'] ?? true,
            status: $data['status'] ?? 'pending',
            reportToId: $ids['reportToId'] ?? 0,
            users: $data['users'] ?? [],
            activeUsers: $data['activeUsers'] ?? [],
            fileUploads: $data['fileUploads'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'profile_photo_path' => $this->profilePhotoPath,
            'job_title' => $this->jobTitle,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt,
            'contact_number' => $this->contactNumber,
            'whatsapp_number' => $this->whatsappNumber,
            'password' => $this->password,
            'locale' => $this->locale,
            'rights' => $this->rights,
            'notifications' => $this->notifications,
            'enabled' => $this->enabled,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'last_login_at' => $this->lastLoginAt,
            'user_form_path' => $this->userFormPath,
            'report_to_id' => $this->reportToId,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'deleted_by' => $this->deletedBy,
            'token' => $this->token,
            'created_at' => $this->createdAt,
            'id' => $this->id,
            'user_id' => $this->userId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'payload' => $this->payload,
            'last_activity' => $this->lastActivity,
        ];
    }
}
