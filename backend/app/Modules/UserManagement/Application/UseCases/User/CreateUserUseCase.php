<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Models\User;
use App\Modules\Common\Application\UseCases\FileUploadUseCase;
use App\Modules\UserManagement\Application\DTOs\UserData;
use App\Modules\UserManagement\Domain\Services\UserService;
use Illuminate\Http\UploadedFile;

final readonly class CreateUserUseCase
{
    private const MODULE = 'user-management';

    private const FILE_FIELDS = ['profilePhotoPath', 'userFormPath'];

    public function __construct(
        private UserService $userService,
        private FileUploadUseCase $fileUploadUseCase
    ) {}

    public function execute(array $input): User
    {
        $files = $this->extractFiles($input);
        $cleanInput = $this->removeFileFields($input);

        $userData = UserData::fromArray($cleanInput);
        $user = $this->userService->createUser($userData);

        foreach ($files as $key => $file) {
            $this->uploadFile($file, $this->mapToColumn($key), $user);
        }

        return $user;
    }

    private function extractFiles(array $input): array
    {
        $files = [];
        foreach (self::FILE_FIELDS as $field) {
            $value = $input[$field] ?? null;
            if ($value instanceof UploadedFile) {
                $files[$field] = $value;
            }
            unset($input[$field]);
        }

        return $files;
    }

    private function removeFileFields(array $input): array
    {
        foreach (self::FILE_FIELDS as $field) {
            unset($input[$field]);
        }

        return $input;
    }

    private function mapToColumn(string $field): string
    {
        return match ($field) {
            'profilePhotoPath' => 'profile_photo_path',
            'userFormPath' => 'user_form_path',
        };
    }

    private function uploadFile(?UploadedFile $uploadedFile, string $column, User $user): void
    {
        if (!$uploadedFile instanceof UploadedFile) {
            return;
        }

        $fileUpload = $this->fileUploadUseCase->execute($uploadedFile, self::MODULE, $user->id);
        $user->update([$column => $fileUpload->path]);
    }
}
