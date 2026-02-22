<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Exceptions\AuthenticationException;
use Plume\Exceptions\RateLimitException;
use Plume\Exceptions\ValidationException;
use Plume\Exceptions\XApiException;
use Plume\Http\XHttpClient;

function makeClient(?string $accessToken = null, ?string $refreshToken = null): XHttpClient
{
    return new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
        accessToken: $accessToken,
        refreshToken: $refreshToken,
        clientId: 'test-client-id',
    );
}

it('throws RateLimitException on 429', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'title' => 'Too Many Requests',
            'detail' => 'Rate limit exceeded',
        ], 429, [
            'x-rate-limit-limit' => '300',
            'x-rate-limit-remaining' => '0',
            'x-rate-limit-reset' => (string) (time() + 900),
        ]),
    ]);

    makeClient()->get('/2/tweets/123');
})->throws(RateLimitException::class, 'Rate limit exceeded');

it('throws AuthenticationException on 401', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'title' => 'Unauthorized',
            'detail' => 'Invalid token',
        ], 401),
    ]);

    makeClient()->get('/2/tweets/123');
})->throws(AuthenticationException::class, 'Invalid token');

it('throws AuthenticationException on 403', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'title' => 'Forbidden',
            'detail' => 'Not authorized',
        ], 403),
    ]);

    makeClient()->get('/2/tweets/123');
})->throws(AuthenticationException::class, 'Not authorized');

it('throws ValidationException on 400', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'title' => 'Invalid Request',
            'detail' => 'Missing required field',
        ], 400),
    ]);

    makeClient()->get('/2/tweets/123');
})->throws(ValidationException::class, 'Missing required field');

it('throws XApiException on other errors', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'detail' => 'Internal Server Error',
        ], 500),
    ]);

    makeClient()->get('/2/tweets/123');
})->throws(XApiException::class, 'Internal Server Error');

it('includes rate limit headers in exception', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'detail' => 'Rate limit exceeded',
        ], 429, [
            'x-rate-limit-limit' => '300',
            'x-rate-limit-remaining' => '0',
            'x-rate-limit-reset' => '1700000000',
        ]),
    ]);

    try {
        makeClient()->get('/2/tweets/123');
    } catch (RateLimitException $e) {
        expect($e->rateLimitHeaders)->toHaveKey('x-rate-limit-limit', '300')
            ->and($e->rateLimitHeaders)->toHaveKey('x-rate-limit-remaining', '0')
            ->and($e->resetTimestamp)->toBe(1700000000);
    }
});

it('attempts token refresh on 401 when refresh token is available', function (): void {
    $callCount = 0;

    Http::fake([
        'api.x.com/2/oauth2/token' => Http::response([
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_in' => 7200,
        ], 200),
        'api.x.com/2/users/me' => function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return Http::response(['detail' => 'Unauthorized'], 401);
            }

            return Http::response([
                'data' => ['id' => '123', 'name' => 'Test', 'username' => 'test'],
            ], 200);
        },
    ]);

    $refreshed = null;
    $client = makeClient('old-access-token', 'old-refresh-token')
        ->withTokenRefreshedCallback(function (array $tokens) use (&$refreshed): void {
            $refreshed = $tokens;
        });

    $result = $client->get('/2/users/me');

    expect($result['data']['username'])->toBe('test')
        ->and($refreshed)->not->toBeNull()
        ->and($refreshed['access_token'])->toBe('new-access-token')
        ->and($refreshed['refresh_token'])->toBe('new-refresh-token');
});

it('does not retry more than once on 401', function (): void {
    Http::fake([
        'api.x.com/2/oauth2/token' => Http::response([
            'access_token' => 'still-invalid',
            'refresh_token' => 'new-refresh',
            'expires_in' => 7200,
        ], 200),
        'api.x.com/2/users/me' => Http::response(['detail' => 'Unauthorized'], 401),
    ]);

    $client = makeClient('old-token', 'old-refresh');

    $client->get('/2/users/me');
})->throws(AuthenticationException::class);

it('throws immediately when token refresh itself fails', function (): void {
    $requestCount = 0;

    Http::fake([
        'api.x.com/2/oauth2/token' => Http::response(['error' => 'invalid_grant'], 400),
        'api.x.com/2/users/me' => function () use (&$requestCount) {
            $requestCount++;

            return Http::response(['detail' => 'Unauthorized'], 401);
        },
    ]);

    try {
        makeClient('old-token', 'old-refresh')->get('/2/users/me');
    } catch (AuthenticationException) {
        // Should only hit the API once (no retry after failed refresh)
        expect($requestCount)->toBe(1);

        return;
    }

    $this->fail('Expected AuthenticationException to be thrown');
});

it('returns json from successful requests', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'data' => ['id' => '123', 'text' => 'Hello'],
        ], 200),
    ]);

    $result = makeClient()->get('/2/tweets/123');

    expect($result)->toHaveKey('data')
        ->and($result['data'])->toHaveKey('id', '123');
});

it('sends POST data correctly', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'data' => ['id' => '456', 'text' => 'New post'],
        ], 200),
    ]);

    $result = makeClient('my-token')->post('/2/tweets', ['text' => 'New post']);

    expect($result['data']['text'])->toBe('New post');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.x.com/2/tweets'
            && $request['text'] === 'New post';
    });
});

it('uses Basic Auth for token refresh when client_secret is provided', function (): void {
    $callCount = 0;

    Http::fake([
        'api.x.com/2/oauth2/token' => Http::response([
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_in' => 7200,
        ], 200),
        'api.x.com/2/users/me' => function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return Http::response(['detail' => 'Unauthorized'], 401);
            }

            return Http::response([
                'data' => ['id' => '123', 'name' => 'Test', 'username' => 'test'],
            ], 200);
        },
    ]);

    $client = new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        accessToken: 'old-access-token',
        refreshToken: 'old-refresh-token',
        clientId: 'test-client-id',
        clientSecret: 'test-client-secret',
    );

    $client->get('/2/users/me');

    // Verify that Basic Auth header was sent
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.x.com/2/oauth2/token'
            && $request->hasHeader('Authorization')
            && str_starts_with($request->header('Authorization')[0], 'Basic ');
    });
});

it('does not send Authorization header when access token is empty string', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'data' => ['id' => '123'],
        ], 200),
    ]);

    $client = new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        accessToken: '',
    );

    $client->get('/2/tweets/123');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.x.com/2/tweets/123'
            && ! $request->hasHeader('Authorization');
    });
});

it('does not send Authorization header when access token is null', function (): void {
    Http::fake([
        'api.x.com/*' => Http::response([
            'data' => ['id' => '123'],
        ], 200),
    ]);

    $client = new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        accessToken: null,
    );

    $client->get('/2/tweets/123');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.x.com/2/tweets/123'
            && ! $request->hasHeader('Authorization');
    });
});
