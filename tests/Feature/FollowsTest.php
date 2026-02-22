<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function followsClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('follows a user', function (): void {
    Http::fake([
        'api.x.com/2/users/123/following' => Http::response(['data' => ['following' => true]]),
    ]);

    followsClient()->follow('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['target_user_id'] === '456');
});

it('unfollows a user', function (): void {
    Http::fake([
        'api.x.com/2/users/123/following/456' => Http::response(['data' => ['following' => false]]),
    ]);

    followsClient()->unfollow('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('gets followers', function (): void {
    Http::fake([
        'api.x.com/2/users/123/followers*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'Follower', 'username' => 'follower'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = followsClient()->followers('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->username)->toBe('follower');
});

it('gets following', function (): void {
    Http::fake([
        'api.x.com/2/users/123/following*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'Following', 'username' => 'following'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = followsClient()->following('123');

    expect($result->data)->toHaveCount(1);
});
