<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Models\User;
use App\Modules\Common\Application\UseCases\DeleteFileUseCase;
use App\Modules\Common\Application\UseCases\FileUploadUseCase;
use App\Modules\UserManagement\Application\DTOs\UserData;
use App\Modules\UserManagement\Domain\Services\UserService;
use Illuminate\Http\UploadedFile;

final readonly class UpdateUserUseCase
{
    private const MODULE = 'user-management';

    private const FILE_FIELDS = [
        'profilePhotoPath' => 'profile_photo_path',
        'userFormPath' => 'user_form_path',
    ];

    public function __construct(
        private UserService $userService,
        private FileUploadUseCase $fileUploadUseCase,
        private DeleteFileUseCase $deleteFileUseCase,
    ) {}

    public function execute(int $id, array $input): User
    {
        $files = $this->extractFiles($input);
        $clean = $this->removeFileFields($input);

        $userData = UserData::fromArray($clean);
        $user = $this->userService->updateUser($id, $userData);

        foreach ($files as $field => [$file, $present]) {
            $column = self::FILE_FIELDS[$field];
            $this->handleFile($user, $column, $file, $present);
        }

        return $user;
    }

    private function extractFiles(array $input): array
    {
        $result = [];
        foreach (array_keys(self::FILE_FIELDS) as $field) {
            $value = $input[$field] ?? null;
            $result[$field] = [$value, array_key_exists($field, $input)];
        }

        return $result;
    }

    private function removeFileFields(array $input): array
    {
        foreach (array_keys(self::FILE_FIELDS) as $field) {
            unset($input[$field]);
        }

        return $input;
    }

    private function handleFile(User $user, string $column, mixed $value, bool $present): void
    {
        if ($value instanceof UploadedFile) {
            $this->replaceFile($user, $column, $value);
        } elseif ($present && ($value === null || $value === '')) {
            $this->clearFile($user, $column);
        }
    }

    private function replaceFile(User $user, string $column, UploadedFile $uploadedFile): void
    {
        if ($user->$column) {
            $this->deleteFileUseCase->execute($user->$column);
        }
        $fileUpload = $this->fileUploadUseCase->execute($uploadedFile, self::MODULE, $user->id);
        $user->update([$column => $fileUpload->path]);
    }

    private function clearFile(User $user, string $column): void
    {
        if ($user->$column) {
            $this->deleteFileUseCase->execute($user->$column);
            $user->update([$column => null]);
        }
    }
}
