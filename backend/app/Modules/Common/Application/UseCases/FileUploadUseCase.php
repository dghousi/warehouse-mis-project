<?php

declare(strict_types=1);

namespace App\Modules\Common\Application\UseCases;

use App\Modules\Common\Domain\Entities\FileUpload;
use App\Modules\Common\Domain\Services\FileUploadService;
use Illuminate\Http\UploadedFile;

final readonly class FileUploadUseCase
{
    public function __construct(private FileUploadService $fileUploadService) {}

    public function execute(UploadedFile $uploadedFile, string $module, ?int $userId = null): FileUpload
    {
        return $this->fileUploadService->uploadFile($uploadedFile, $module, $userId);
    }
}
