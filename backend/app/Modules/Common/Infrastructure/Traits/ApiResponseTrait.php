<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Traits;

trait ApiResponseTrait
{
    protected function successResponse($data = null, string $message = 'Operation successful', int $code = 200)
    {
        return response()->json(data: [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], status: $code);
    }

    protected function errorResponse(string $code, string $message = 'An error occurred', $details = null, int $httpCode = 400)
    {
        return response()->json(data: [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], status: $httpCode);
    }
}
