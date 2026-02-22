<?php

declare(strict_types=1);

use Plume\Data\Includes;
use Plume\Data\Post;
use Plume\Data\PostMetrics;

it('constructs with required fields', function (): void {
    $post = new Post(id: '123', text: 'Hello world');

    expect($post->id)->toBe('123')
        ->and($post->text)->toBe('Hello world')
        ->and($post->authorId)->toBeNull()
        ->and($post->publicMetrics)->toBeNull();
});

it('constructs with all fields', function (): void {
    $metrics = new PostMetrics(
        retweetCount: 10,
        replyCount: 5,
        likeCount: 100,
        quoteCount: 2,
        bookmarkCount: 15,
        impressionCount: 5000,
    );

    $post = new Post(
        id: '456',
        text: 'Full post',
        authorId: '789',
        conversationId: '456',
        inReplyToUserId: '321',
        createdAt: '2024-01-01T00:00:00Z',
        lang: 'en',
        source: 'Twitter Web App',
        replySettings: 'everyone',
        possiblySensitive: false,
        publicMetrics: $metrics,
        referencedTweets: [['type' => 'replied_to', 'id' => '100']],
    );

    expect($post->id)->toBe('456')
        ->and($post->authorId)->toBe('789')
        ->and($post->publicMetrics->likeCount)->toBe(100)
        ->and($post->referencedTweets)->toHaveCount(1);
});

it('throws LogicException when calling action without provider', function (): void {
    $post = new Post(id: '123', text: 'Hello');
    $post->delete();
})->throws(LogicException::class, 'Post requires an XApiProvider');

it('can have includes attached', function (): void {
    $includes = new Includes(users: [], tweets: [], media: [], polls: [], places: []);
    $post = new Post(id: '1', text: 'test', includes: $includes);

    expect($post->includes)->toBeInstanceOf(Includes::class);
});
