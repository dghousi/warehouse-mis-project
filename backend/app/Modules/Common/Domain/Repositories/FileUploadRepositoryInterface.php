<?php

declare(strict_types=1);

namespace App\Modules\Common\Domain\Repositories;

use App\Modules\Common\Domain\Entities\FileUpload;

interface FileUploadRepositoryInterface
{
    public function create(array $data): FileUpload;

    public function delete(string $path): void;
}
