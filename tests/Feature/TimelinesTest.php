<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Enums\Exclude;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function timelinesClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('gets user timeline', function (): void {
    Http::fake([
        'api.x.com/2/users/123/tweets*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'My tweet'],
                ['id' => '2', 'text' => 'Another tweet'],
            ],
            'meta' => ['result_count' => 2, 'next_token' => 'next456'],
        ]),
    ]);

    $result = timelinesClient()->userTimeline('123');

    expect($result->data)->toHaveCount(2)
        ->and($result->resultCount)->toBe(2)
        ->and($result->hasNextPage())->toBeTrue();
});

it('gets user timeline with exclude', function (): void {
    Http::fake([
        'api.x.com/2/users/123/tweets*' => Http::response([
            'data' => [['id' => '1', 'text' => 'Original']],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    timelinesClient()->userTimeline('123', exclude: [Exclude::Replies, Exclude::Retweets]);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'exclude=replies%2Cretweets'));
});

it('gets mentions timeline', function (): void {
    Http::fake([
        'api.x.com/2/users/123/mentions*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => '@user mentioned you'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = timelinesClient()->mentionsTimeline('123');

    expect($result->data)->toHaveCount(1);
});

it('gets home timeline', function (): void {
    Http::fake([
        'api.x.com/2/users/123/timelines/reverse_chronological*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'Home tweet'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = timelinesClient()->homeTimeline('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->text)->toBe('Home tweet');
});
