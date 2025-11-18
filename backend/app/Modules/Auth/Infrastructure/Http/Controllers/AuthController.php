<?php

declare(strict_types=1);

namespace App\Modules\Auth\Infrastructure\Http\Controllers;

use App\Models\User;
use App\Modules\Common\Infrastructure\Http\Controllers\BaseApiController;
use App\Modules\UserManagement\Infrastructure\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthController extends BaseApiController
{
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::guard('sanctum')->user();

        return $this->successResponse(
            data: $user ? new UserResource($user) : null,
            message: __('UserManagement::messages.user.fetched')
        );
    }

    public function logout(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::guard('sanctum')->user();

        if ($user) {
            $token = $user->currentAccessToken();

            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            $user->tokens()->delete();
        }

        return $this->successResponse(
            data: null,
            message: __('Auth::messages.logout.success')
        );
    }
}
