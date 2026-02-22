<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Data\Post;
use Plume\Enums\TweetField;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function postsClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('creates a post', function (): void {
    Http::fake([
        'api.x.com/2/tweets' => Http::response([
            'data' => ['id' => '123', 'text' => 'Hello world'],
        ]),
    ]);

    $post = postsClient()->createPost('Hello world');

    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->id)->toBe('123')
        ->and($post->text)->toBe('Hello world');
});

it('creates a post with options', function (): void {
    Http::fake([
        'api.x.com/2/tweets' => Http::response([
            'data' => ['id' => '456', 'text' => 'Reply text'],
        ]),
    ]);

    $post = postsClient()->createPost('Reply text', [
        'reply' => ['in_reply_to_tweet_id' => '100'],
    ]);

    expect($post->id)->toBe('456');

    Http::assertSent(fn ($r) => $r['reply']['in_reply_to_tweet_id'] === '100');
});

it('deletes a post', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123' => Http::response(['data' => ['deleted' => true]]),
    ]);

    postsClient()->deletePost('123');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE' && str_contains($r->url(), '/2/tweets/123'));
});

it('gets a single post', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123*' => Http::response([
            'data' => ['id' => '123', 'text' => 'Found it'],
        ]),
    ]);

    $post = postsClient()->getPost('123');

    expect($post->id)->toBe('123')
        ->and($post->text)->toBe('Found it');
});

it('gets a post with field expansion', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123*' => Http::response([
            'data' => [
                'id' => '123',
                'text' => 'Hello',
                'author_id' => '456',
                'public_metrics' => [
                    'retweet_count' => 5,
                    'reply_count' => 2,
                    'like_count' => 10,
                    'quote_count' => 1,
                    'bookmark_count' => 3,
                    'impression_count' => 500,
                ],
            ],
        ]),
    ]);

    $post = postsClient()->getPost('123', tweetFields: [TweetField::AuthorId, TweetField::PublicMetrics]);

    expect($post->authorId)->toBe('456')
        ->and($post->publicMetrics)->not->toBeNull()
        ->and($post->publicMetrics->likeCount)->toBe(10);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'tweet.fields=author_id%2Cpublic_metrics'));
});

it('gets multiple posts', function (): void {
    Http::fake([
        'api.x.com/2/tweets?*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'First'],
                ['id' => '2', 'text' => 'Second'],
            ],
        ]),
    ]);

    $posts = postsClient()->getPosts(['1', '2']);

    expect($posts)->toHaveCount(2)
        ->and($posts[0]->id)->toBe('1')
        ->and($posts[1]->id)->toBe('2');
});

it('hides a reply', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123/hidden' => Http::response(['data' => ['hidden' => true]]),
    ]);

    postsClient()->hideReply('123');

    Http::assertSent(fn ($r) => $r->method() === 'PUT' && $r['hidden'] === true);
});

it('unhides a reply', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123/hidden' => Http::response(['data' => ['hidden' => false]]),
    ]);

    postsClient()->unhideReply('123');

    Http::assertSent(fn ($r) => $r->method() === 'PUT' && $r['hidden'] === false);
});
