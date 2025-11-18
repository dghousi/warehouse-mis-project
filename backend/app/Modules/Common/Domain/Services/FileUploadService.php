<?php

declare(strict_types=1);

namespace App\Modules\Common\Domain\Services;

use App\Modules\Common\Application\Exceptions\ApiException;
use App\Modules\Common\Domain\Entities\FileUpload;
use App\Modules\Common\Domain\Repositories\FileUploadRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class FileUploadService
{
    public function __construct(
        private FileUploadRepositoryInterface $fileUploadRepository,
    ) {}

    public function uploadFile(UploadedFile $uploadedFile, string $module, ?int $userId = null): FileUpload
    {
        $this->enforceRateLimit($module, $userId);
        $this->validateModule($module);

        return DB::transaction(fn (): FileUpload => $this->storeFileAndRecord($uploadedFile, $module, $userId));
    }

    public function deleteFile(string $path): void
    {
        if (in_array($path, ['', '0', '0'], true)) {
            return;
        }

        DB::transaction(fn () => $this->deleteFileAndRecord($path));
    }

    private function getConfig(string $path, $default = null)
    {
        return config("uploads.{$path}", $default);
    }

    private function enforceRateLimit(string $module, ?int $userId): void
    {
        $key = $userId
            ? "uploads:user:{$userId}:{$module}"
            : 'uploads:ip:'.request()->ip().":{$module}";

        $maxAttempts = $this->getConfig('rate_limit.max_attempts', 5);
        $decaySeconds = $this->getConfig('rate_limit.decay_seconds', 60);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            throw new ApiException(
                errorCode: 'UPLOAD_RATE_LIMITED',
                message: "Too many uploads. Please try again in {$retryAfter} seconds.",
                httpCode: 429,
            );
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    private function validateModule(string $module): void
    {
        if (!$this->getConfig("allowed_modules.{$module}")) {
            throw new ApiException(
                errorCode: 'INVALID_MODULE',
                message: 'The specified module is not allowed.',
                httpCode: 422
            );
        }
    }

    private function storeFileAndRecord(UploadedFile $uploadedFile, string $module, ?int $userId): FileUpload
    {
        $directory = $this->resolveModuleDirectory($module);
        $path = $this->storeFile($uploadedFile, $directory);

        return $this->fileUploadRepository->create($this->buildFileMetadata($uploadedFile, $path, $module, $userId));
    }

    private function deleteFileAndRecord(string $path): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        $this->fileUploadRepository->delete($path);
    }

    private function resolveModuleDirectory(string $module): string
    {
        return $this->getConfig("allowed_modules.{$module}.directory", 'common/uploads');
    }

    private function storeFile(UploadedFile $uploadedFile, string $directory): string
    {
        $filename = Str::uuid().'.'.$uploadedFile->getClientOriginalExtension();

        return $uploadedFile->storeAs($directory, $filename, 'public');
    }

    private function buildFileMetadata(UploadedFile $uploadedFile, string $path, string $module, ?int $userId): array
    {
        return [
            'user_id' => $userId,
            'path' => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'module' => $module,
        ];
    }
}
