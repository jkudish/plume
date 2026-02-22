<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function retweetsClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('retweets a post', function (): void {
    Http::fake([
        'api.x.com/2/users/123/retweets' => Http::response(['data' => ['retweeted' => true]]),
    ]);

    retweetsClient()->retweet('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['tweet_id'] === '456');
});

it('undoes a retweet', function (): void {
    Http::fake([
        'api.x.com/2/users/123/retweets/456' => Http::response(['data' => ['retweeted' => false]]),
    ]);

    retweetsClient()->undoRetweet('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('gets users who retweeted', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123/retweeted_by*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'Retweeter', 'username' => 'retweeter'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = retweetsClient()->retweetedBy('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->username)->toBe('retweeter');
});

it('gets quote tweets', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123/quote_tweets*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'Quote tweet'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = retweetsClient()->quoteTweets('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->text)->toBe('Quote tweet');
});
