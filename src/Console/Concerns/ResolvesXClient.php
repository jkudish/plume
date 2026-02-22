<?php

declare(strict_types=1);

namespace Plume\Console\Concerns;

use Plume\Contracts\XApiProvider;

trait ResolvesXClient
{
    /**
     * Resolve an authenticated X API client.
     *
     * Returns the XApiProvider instance or FAILURE if not configured.
     */
    protected function resolveClient(): XApiProvider|int
    {
        $bearerToken = config('x.bearer_token');

        if (! is_string($bearerToken) || $bearerToken === '') {
            $this->error('No X API bearer token configured. Set X_BEARER_TOKEN in your .env file.');

            return self::FAILURE;
        }

        return app(XApiProvider::class);
    }

    /**
     * Resolve the authenticated user's ID.
     *
     * Calls /me to get the current user. Returns the user ID or FAILURE.
     */
    protected function resolveUserId(XApiProvider $client): string|int
    {
        try {
            return $client->me()->id;
        } catch (\Throwable $e) {
            $this->error("Failed to resolve user identity: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
