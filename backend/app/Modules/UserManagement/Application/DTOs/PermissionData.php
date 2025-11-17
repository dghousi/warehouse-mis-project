<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\DTOs;

final readonly class PermissionData
{
    public function __construct(
        public string $name,
        public string $displayNameEn,
        public string $displayNamePs,
        public string $displayNameDr,
        public ?string $guardName = 'web',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            displayNameEn: $data['displayNameEn'] ?? '',
            displayNamePs: $data['displayNamePs'] ?? '',
            displayNameDr: $data['displayNameDr'] ?? '',
            guardName: $data['guardName'] ?? 'web',
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'display_name_en' => $this->displayNameEn,
            'display_name_ps' => $this->displayNamePs,
            'display_name_dr' => $this->displayNameDr,
            'guard_name' => $this->guardName,
        ];
    }
}
