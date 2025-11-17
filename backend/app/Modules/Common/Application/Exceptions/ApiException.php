<?php

declare(strict_types=1);

namespace App\Modules\Common\Application\Exceptions;

use Exception;

final class ApiException extends Exception
{
    public function __construct(
        public string $errorCode,
        string $message = '',
        public ?array $details = null,
        int $httpCode = 400
    ) {
        parent::__construct(message: $message ?: $errorCode, code: $httpCode);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): mixed
    {
        return $this->details;
    }
}
