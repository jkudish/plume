<?php

declare(strict_types=1);

namespace Plume\Exceptions;

class RateLimitException extends XApiException
{
    public readonly int $resetTimestamp;

    /**
     * @param  array<string, mixed>  $response
     * @param  array<string, string>  $rateLimitHeaders
     */
    public function __construct(
        string $message,
        array $response = [],
        array $rateLimitHeaders = [],
    ) {
        parent::__construct($message, 429, $response, $rateLimitHeaders);

        $this->resetTimestamp = (int) ($rateLimitHeaders['x-rate-limit-reset'] ?? time());
    }

    public function retryAfterSeconds(): int
    {
        return max(0, $this->resetTimestamp - time());
    }
}
