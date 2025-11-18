<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Repositories;

use App\Modules\Common\Domain\Entities\FileUpload;
use App\Modules\Common\Domain\Repositories\FileUploadRepositoryInterface;

final class EloquentFileUploadRepository implements FileUploadRepositoryInterface
{
    public function create(array $data): FileUpload
    {
        return FileUpload::create($data);
    }

    public function delete(string $path): void
    {
        $fileUpload = FileUpload::where('path', $path)->first();

        if ($fileUpload) {
            $fileUpload->delete();
        }
    }
}
