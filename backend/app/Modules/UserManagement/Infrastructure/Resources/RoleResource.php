<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

final class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = App::getLocale();

        $displayNames = [
            'en' => $this->display_name_en,
            'ps' => $this->display_name_ps,
            'dr' => $this->display_name_dr,
        ];

        $displayName = $displayNames[$locale] ?? $this->display_name_en;

        return $this->filterEmpty([
            'id' => $this->id,
            'name' => $this->name,
            'displayName' => $displayName,
            'displayNameEn' => $this->display_name_en,
            'displayNamePs' => $this->display_name_ps,
            'displayNameDr' => $this->display_name_dr,
            'permissions' => $this->getPermissions(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ]);
    }

    private function getPermissions()
    {
        return $this->whenLoaded('permissions', fn () => PermissionResource::collection($this->permissions));
    }

    private function filterEmpty(array $data): array
    {
        return array_filter($data, fn ($value): bool => !in_array($value, [null, '', []], true) && !($value instanceof Collection && $value->isEmpty()));
    }
}
