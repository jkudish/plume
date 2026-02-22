<?php

declare(strict_types=1);

use Plume\Facades\X;

beforeEach(function (): void {
    config(['x.bearer_token' => 'test-token']);
    X::fake();
});

// ── LikeCommand ──────────────────────────────────────────────

it('likes a tweet', function (): void {
    $this->artisan('plume:like', ['id' => '123'])
        ->assertSuccessful()
        ->expectsOutputToContain('Tweet liked successfully');
});

// ── UnlikeCommand ────────────────────────────────────────────

it('unlikes a tweet', function (): void {
    $this->artisan('plume:unlike', ['id' => '123'])
        ->assertSuccessful()
        ->expectsOutputToContain('Tweet unliked successfully');
});

// ── FollowCommand ────────────────────────────────────────────

it('follows a user', function (): void {
    $this->artisan('plume:follow', ['id' => '456'])
        ->assertSuccessful()
        ->expectsOutputToContain('User followed successfully');
});

// ── UnfollowCommand ──────────────────────────────────────────

it('unfollows a user with force', function (): void {
    $this->artisan('plume:unfollow', ['id' => '456', '--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('User unfollowed successfully');
});

// ── RetweetCommand ───────────────────────────────────────────

it('retweets a tweet', function (): void {
    $this->artisan('plume:retweet', ['id' => '123'])
        ->assertSuccessful()
        ->expectsOutputToContain('Tweet retweeted successfully');
});

// ── UnretweetCommand ─────────────────────────────────────────

it('undoes a retweet', function (): void {
    $this->artisan('plume:unretweet', ['id' => '123'])
        ->assertSuccessful()
        ->expectsOutputToContain('Retweet undone successfully');
});

// ── BookmarkCommand ──────────────────────────────────────────

it('bookmarks a tweet', function (): void {
    $this->artisan('plume:bookmark', ['id' => '123'])
        ->assertSuccessful()
        ->expectsOutputToContain('Tweet bookmarked successfully');
});

// ── UnbookmarkCommand ────────────────────────────────────────

it('removes a bookmark', function (): void {
    $this->artisan('plume:unbookmark', ['id' => '123'])
        ->assertSuccessful()
        ->expectsOutputToContain('Bookmark removed successfully');
});

// ── BlockCommand ─────────────────────────────────────────────

it('blocks a user with force', function (): void {
    $this->artisan('plume:block', ['id' => '456', '--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('User blocked successfully');
});

// ── UnblockCommand ───────────────────────────────────────────

it('unblocks a user with force', function (): void {
    $this->artisan('plume:unblock', ['id' => '456', '--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('User unblocked successfully');
});

// ── MuteCommand ──────────────────────────────────────────────

it('mutes a user with force', function (): void {
    $this->artisan('plume:mute', ['id' => '456', '--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('User muted successfully');
});

// ── UnmuteCommand ────────────────────────────────────────────

it('unmutes a user with force', function (): void {
    $this->artisan('plume:unmute', ['id' => '456', '--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('User unmuted successfully');
});
