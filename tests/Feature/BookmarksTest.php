<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function bookmarksClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('bookmarks a tweet', function (): void {
    Http::fake([
        'api.x.com/2/users/123/bookmarks' => Http::response(['data' => ['bookmarked' => true]]),
    ]);

    bookmarksClient()->bookmark('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['tweet_id'] === '456');
});

it('removes a bookmark', function (): void {
    Http::fake([
        'api.x.com/2/users/123/bookmarks/456' => Http::response(['data' => ['bookmarked' => false]]),
    ]);

    bookmarksClient()->removeBookmark('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('lists bookmarks', function (): void {
    Http::fake([
        'api.x.com/2/users/123/bookmarks*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'Bookmarked post'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = bookmarksClient()->bookmarks('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->text)->toBe('Bookmarked post');
});
