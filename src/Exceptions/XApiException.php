<?php

declare(strict_types=1);

namespace Plume\Exceptions;

use RuntimeException;

class XApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $response
     * @param  array<string, string>  $rateLimitHeaders
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly array $response = [],
        public readonly array $rateLimitHeaders = [],
    ) {
        parent::__construct($message, $statusCode);
    }
}
