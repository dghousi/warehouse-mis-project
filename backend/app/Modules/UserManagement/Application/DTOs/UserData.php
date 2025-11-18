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
        public ?int $reportToId,
        public string $email,
        public ?string $emailVerifiedAt,
        public ?string $contactNumber,
        public ?string $whatsappNumber,
        public ?string $password,
        public ?int $mainOrganizationId,
        public ?string $remarks,
        public ?string $lastLoginAt,
        public ?string $userFormPath,
        public ?int $createdBy,
        public ?int $updatedBy,
        public array $roles = [],
        public array $permissions = [],
        public string $locale = 'en',
        public string $rights = 'review',
        public bool $notifications = true,
        public bool $enabled = true,
        public string $status = 'pending',
    ) {}

    public static function fromArray(array $data): self
    {
        $ids = self::castIds(['reportToId', 'mainOrganizationId', 'createdBy', 'updatedBy'], $data);

        return new self(
            firstName: $data['firstName'] ?? '',
            lastName: $data['lastName'] ?? null,
            profilePhotoPath: $data['profilePhotoPath'] ?? null,
            jobTitle: $data['jobTitle'] ?? '',
            reportToId: $ids['reportToId'] ?? 0,
            email: $data['email'] ?? '',
            emailVerifiedAt: $data['emailVerifiedAt'] ?? null,
            contactNumber: $data['contactNumber'] ?? null,
            whatsappNumber: $data['whatsappNumber'] ?? null,
            password: $data['password'] ?? null,
            mainOrganizationId: $ids['mainOrganizationId'] ?? 0,
            remarks: $data['remarks'] ?? null,
            lastLoginAt: $data['lastLoginAt'] ?? null,
            userFormPath: $data['userFormPath'] ?? null,
            createdBy: $ids['createdBy'] ?? 0,
            updatedBy: $ids['updatedBy'] ?? 0,
            roles: $data['roles'] ?? [],
            permissions: $data['permissions'] ?? [],
            locale: $data['locale'] ?? 'en',
            rights: $data['rights'] ?? 'review',
            notifications: $data['notifications'] ?? true,
            enabled: $data['enabled'] ?? true,
            status: $data['status'] ?? 'pending',
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'job_title' => $this->jobTitle,
            'report_to_id' => $this->reportToId,
            'email' => $this->email,
            'contact_number' => $this->contactNumber,
            'whatsapp_number' => $this->whatsappNumber,
            'password' => $this->password,
            'locale' => $this->locale,
            'main_organization_id' => $this->mainOrganizationId,
            'rights' => $this->rights,
            'notifications' => $this->notifications,
            'enabled' => $this->enabled,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'last_login_at' => $this->lastLoginAt,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'roles' => $this->roles,
            'permissions' => $this->permissions,
        ];
    }
}
