<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Application\UseCases\User;

use App\Models\User;

final class SetUserLocaleUseCase
{
    public function execute(User $user, string $locale): void
    {
        $user->update(['locale' => $locale]);
    }
}
