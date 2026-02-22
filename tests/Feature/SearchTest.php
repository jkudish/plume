<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Enums\Granularity;
use Plume\Enums\SortOrder;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function searchClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('searches recent tweets', function (): void {
    Http::fake([
        'api.x.com/2/tweets/search/recent*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'Laravel is great'],
            ],
            'meta' => ['result_count' => 1, 'next_token' => 'next123'],
        ]),
    ]);

    $result = searchClient()->searchRecent('laravel');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->text)->toBe('Laravel is great')
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextToken)->toBe('next123');
});

it('searches with sort order', function (): void {
    Http::fake([
        'api.x.com/2/tweets/search/recent*' => Http::response([
            'data' => [['id' => '1', 'text' => 'Sorted']],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    searchClient()->searchRecent('test', sortOrder: SortOrder::Recency);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'sort_order=recency'));
});

it('searches full archive', function (): void {
    Http::fake([
        'api.x.com/2/tweets/search/all*' => Http::response([
            'data' => [['id' => '1', 'text' => 'Old tweet']],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = searchClient()->searchAll('historic query');

    expect($result->data)->toHaveCount(1);
});

it('counts recent tweets', function (): void {
    Http::fake([
        'api.x.com/2/tweets/counts/recent*' => Http::response([
            'data' => [['start' => '2024-01-01', 'end' => '2024-01-02', 'tweet_count' => 42]],
            'meta' => ['total_tweet_count' => 42],
        ]),
    ]);

    $result = searchClient()->countRecent('laravel', Granularity::Day);

    expect($result['meta']['total_tweet_count'])->toBe(42);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'granularity=day'));
});

it('counts all tweets', function (): void {
    Http::fake([
        'api.x.com/2/tweets/counts/all*' => Http::response([
            'data' => [['tweet_count' => 1000]],
            'meta' => ['total_tweet_count' => 1000],
        ]),
    ]);

    $result = searchClient()->countAll('laravel');

    expect($result['meta']['total_tweet_count'])->toBe(1000);
});
