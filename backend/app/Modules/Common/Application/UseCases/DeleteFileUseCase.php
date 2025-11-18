<?php

declare(strict_types=1);

namespace App\Modules\Common\Application\UseCases;

use App\Modules\Common\Domain\Services\FileUploadService;

final readonly class DeleteFileUseCase
{
    public function __construct(private FileUploadService $fileUploadService) {}

    public function execute(string $path): void
    {
        $this->fileUploadService->deleteFile($path);
    }
}
