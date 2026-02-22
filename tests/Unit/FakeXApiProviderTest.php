<?php

declare(strict_types=1);

use Plume\Testing\FakeXApiProvider;

it('records and asserts post creation', function (): void {
    $fake = new FakeXApiProvider;

    $post = $fake->createPost('Hello world');

    expect($post->text)->toBe('Hello world');
    $fake->assertPostCreated('Hello');
    $fake->assertPostCreated();
});

it('records and asserts post deletion', function (): void {
    $fake = new FakeXApiProvider;

    $fake->deletePost('123');

    $fake->assertPostDeleted('123');
});

it('records and asserts likes', function (): void {
    $fake = new FakeXApiProvider;

    $fake->like('user1', 'tweet1');

    $fake->assertLiked('tweet1');
});

it('records and asserts retweets', function (): void {
    $fake = new FakeXApiProvider;

    $fake->retweet('user1', 'tweet1');

    $fake->assertRetweeted('tweet1');
});

it('records and asserts bookmarks', function (): void {
    $fake = new FakeXApiProvider;

    $fake->bookmark('user1', 'tweet1');

    $fake->assertBookmarked('tweet1');
});

it('records and asserts follows', function (): void {
    $fake = new FakeXApiProvider;

    $fake->follow('user1', 'target1');

    $fake->assertFollowed('target1');
});

it('records and asserts blocks', function (): void {
    $fake = new FakeXApiProvider;

    $fake->block('user1', 'target1');

    $fake->assertBlocked('target1');
});

it('records and asserts mutes', function (): void {
    $fake = new FakeXApiProvider;

    $fake->mute('user1', 'target1');

    $fake->assertMuted('target1');
});

it('records and asserts replies', function (): void {
    $fake = new FakeXApiProvider;

    $fake->createPost('Reply text', [
        'reply' => ['in_reply_to_tweet_id' => 'tweet1'],
    ]);

    $fake->assertRepliedTo('tweet1');
});

it('records and asserts search', function (): void {
    $fake = new FakeXApiProvider;

    $fake->searchRecent('laravel');

    $fake->assertSearched('laravel');
});

it('asserts nothing posted', function (): void {
    $fake = new FakeXApiProvider;

    $fake->assertNothingPosted();
});

it('asserts nothing called', function (): void {
    $fake = new FakeXApiProvider;

    $fake->assertNothingCalled();
});

it('asserts call count', function (): void {
    $fake = new FakeXApiProvider;

    $fake->like('u1', 't1');
    $fake->like('u1', 't2');
    $fake->like('u1', 't3');

    $fake->assertCalledTimes('like', 3);
});

it('can configure return values', function (): void {
    $fake = new FakeXApiProvider;
    $fake->shouldReturn('me', new \Plume\Data\User(id: '42', name: 'Custom', username: 'custom'));

    $user = $fake->me();

    expect($user->id)->toBe('42')
        ->and($user->username)->toBe('custom');
});

it('can configure throwables', function (): void {
    $fake = new FakeXApiProvider;
    $fake->shouldThrow('createPost', new \RuntimeException('API down'));

    $fake->createPost('test');
})->throws(\RuntimeException::class, 'API down');

it('asserts forUser called', function (): void {
    $fake = new FakeXApiProvider;

    $fake->forUser(['access_token' => 'test']);

    $fake->assertForUserCalled();
});
