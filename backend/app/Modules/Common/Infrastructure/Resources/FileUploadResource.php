<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FileUploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_filter([
            'id' => $this->id,
            'userId' => $this->user_id,
            'path' => $this->path,
            'url' => asset("storage/{$this->path}"),
            'originalName' => $this->original_name,
            'mimeType' => $this->mime_type,
            'size' => $this->size,
            'module' => $this->module,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ], fn ($value): bool => !in_array($value, [null, '', []], true));
    }
}
