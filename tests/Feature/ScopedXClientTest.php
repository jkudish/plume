<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

it('creates a scoped client and resolves user id lazily', function (): void {
    Http::fake([
        'api.x.com/2/users/me*' => Http::response([
            'data' => ['id' => '999', 'name' => 'Auth User', 'username' => 'authuser'],
        ]),
        'api.x.com/2/users/999/likes' => Http::response([
            'data' => ['liked' => true],
        ]),
    ]);

    $client = new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));

    $scoped = $client->forUser(['access_token' => 'user-token']);
    $scoped->like('tweet123');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/2/users/me'));
    Http::assertSent(fn ($r) => str_contains($r->url(), '/2/users/999/likes'));
});

it('creates posts through scoped client', function (): void {
    Http::fake([
        'api.x.com/2/tweets' => Http::response([
            'data' => ['id' => '123', 'text' => 'Scoped post'],
        ]),
    ]);

    $client = new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));

    $scoped = $client->forUser(['access_token' => 'user-token']);
    $post = $scoped->createPost('Scoped post');

    expect($post->text)->toBe('Scoped post');
});

it('caches the user id after first resolution', function (): void {
    $meCallCount = 0;

    Http::fake([
        'api.x.com/2/users/me*' => function () use (&$meCallCount) {
            $meCallCount++;

            return Http::response([
                'data' => ['id' => '999', 'name' => 'Auth', 'username' => 'auth'],
            ]);
        },
        'api.x.com/2/users/999/likes' => Http::response(['data' => ['liked' => true]]),
        'api.x.com/2/users/999/retweets' => Http::response(['data' => ['retweeted' => true]]),
    ]);

    $client = new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));

    $scoped = $client->forUser(['access_token' => 'user-token']);
    $scoped->like('tweet1');
    $scoped->retweet('tweet2');

    expect($meCallCount)->toBe(1);
});

it('creates a scoped client from an array of credentials', function (): void {
    Http::fake([
        'api.x.com/2/users/me*' => Http::response([
            'data' => ['id' => '123', 'name' => 'Test', 'username' => 'test'],
        ]),
    ]);

    $client = new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));

    $scoped = $client->forUser([
        'access_token' => 'my-token',
        'refresh_token' => 'my-refresh',
        'expires_at' => '2025-12-31T00:00:00Z',
    ]);

    $user = $scoped->me();

    expect($user->id)->toBe('123');
});
