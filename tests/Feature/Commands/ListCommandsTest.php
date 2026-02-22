<?php

declare(strict_types=1);

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Data\User;
use Plume\Facades\X;

beforeEach(function (): void {
    config(['x.bearer_token' => 'test-token']);
    X::fake();
});

function fakeUserList(): PaginatedResult
{
    return new PaginatedResult(
        data: [
            new User(id: '1', name: 'Alice', username: 'alice'),
            new User(id: '2', name: 'Bob', username: 'bob'),
        ],
        resultCount: 2,
    );
}

function fakePostList(): PaginatedResult
{
    return new PaginatedResult(
        data: [
            new Post(id: '10', text: 'A liked post', createdAt: '2025-01-01T00:00:00Z'),
        ],
        resultCount: 1,
    );
}

// ── FollowersCommand ─────────────────────────────────────────

it('lists followers', function (): void {
    X::fake()->shouldReturn('followers', fakeUserList());

    $this->artisan('plume:followers')
        ->assertSuccessful()
        ->expectsOutputToContain('2 item(s) found');
});

it('shows empty message when no followers', function (): void {
    $this->artisan('plume:followers')
        ->assertSuccessful()
        ->expectsOutputToContain('No followers found');
});

it('lists followers as json', function (): void {
    X::fake()->shouldReturn('followers', fakeUserList());

    $this->artisan('plume:followers', ['--format' => 'json'])
        ->assertSuccessful();
});

// ── FollowingCommand ─────────────────────────────────────────

it('lists following', function (): void {
    X::fake()->shouldReturn('following', fakeUserList());

    $this->artisan('plume:following')
        ->assertSuccessful()
        ->expectsOutputToContain('2 item(s) found');
});

it('shows empty message when no following', function (): void {
    $this->artisan('plume:following')
        ->assertSuccessful()
        ->expectsOutputToContain('No following found');
});

// ── LikesCommand ─────────────────────────────────────────────

it('lists liked tweets', function (): void {
    X::fake()->shouldReturn('likedTweets', fakePostList());

    $this->artisan('plume:likes')
        ->assertSuccessful()
        ->expectsOutputToContain('1 item(s) found');
});

it('shows empty message when no liked tweets', function (): void {
    $this->artisan('plume:likes')
        ->assertSuccessful()
        ->expectsOutputToContain('No liked tweets found');
});

// ── BookmarksCommand ─────────────────────────────────────────

it('lists bookmarked tweets', function (): void {
    X::fake()->shouldReturn('bookmarks', fakePostList());

    $this->artisan('plume:bookmarks')
        ->assertSuccessful()
        ->expectsOutputToContain('1 item(s) found');
});

it('shows empty message when no bookmarks', function (): void {
    $this->artisan('plume:bookmarks')
        ->assertSuccessful()
        ->expectsOutputToContain('No bookmarked tweets found');
});

// ── BlockedCommand ───────────────────────────────────────────

it('lists blocked users', function (): void {
    X::fake()->shouldReturn('blockedUsers', fakeUserList());

    $this->artisan('plume:blocked')
        ->assertSuccessful()
        ->expectsOutputToContain('2 item(s) found');
});

it('shows empty message when no blocked users', function (): void {
    $this->artisan('plume:blocked')
        ->assertSuccessful()
        ->expectsOutputToContain('No blocked users found');
});

// ── MutedCommand ─────────────────────────────────────────────

it('lists muted users', function (): void {
    X::fake()->shouldReturn('mutedUsers', fakeUserList());

    $this->artisan('plume:muted')
        ->assertSuccessful()
        ->expectsOutputToContain('2 item(s) found');
});

it('shows empty message when no muted users', function (): void {
    $this->artisan('plume:muted')
        ->assertSuccessful()
        ->expectsOutputToContain('No muted users found');
});
