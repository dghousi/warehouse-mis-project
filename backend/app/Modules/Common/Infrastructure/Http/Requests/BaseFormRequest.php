<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    protected array $booleanFields = [];

    protected function getBooleanFields(): array
    {
        return $this->booleanFields;
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeInput($this->all()));
    }

    protected function normalizeInput(array $input): array
    {
        $normalized = [];

        foreach ($input as $key => $value) {
            $normalized[$key] = $this->castValue($key, $value);
        }

        return $normalized;
    }

    protected function castValue(string $key, $value)
    {
        if (in_array($key, $this->getBooleanFields(), true)) {
            return match ($value) {
                'true', '1' => true,
                'false', '0' => false,
                default => $value,
            };
        }

        return $value;
    }
}
