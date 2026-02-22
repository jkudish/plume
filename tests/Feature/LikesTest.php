<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function likesClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('likes a tweet', function (): void {
    Http::fake([
        'api.x.com/2/users/123/likes' => Http::response(['data' => ['liked' => true]]),
    ]);

    likesClient()->like('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['tweet_id'] === '456');
});

it('unlikes a tweet', function (): void {
    Http::fake([
        'api.x.com/2/users/123/likes/456' => Http::response(['data' => ['liked' => false]]),
    ]);

    likesClient()->unlike('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('gets liking users', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123/liking_users*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'Liker', 'username' => 'liker'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = likesClient()->likingUsers('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->username)->toBe('liker');
});

it('gets liked tweets', function (): void {
    Http::fake([
        'api.x.com/2/users/123/liked_tweets*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'Liked post'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = likesClient()->likedTweets('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->text)->toBe('Liked post');
});
