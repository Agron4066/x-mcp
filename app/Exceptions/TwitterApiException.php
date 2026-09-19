<?php

namespace App\Exceptions;

use RuntimeException;

class TwitterApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly ?string $responseBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    public function isRateLimited(): bool
    {
        return $this->statusCode === 429;
    }

    public function isUnauthorized(): bool
    {
        return $this->statusCode === 401;
    }
}
