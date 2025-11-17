<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx',
            ],
            'module' => [
                'required',
                'string',
                Rule::in(array_keys(config('uploads.allowed_modules', []))),
            ],
        ];
    }
}
