<?php

declare(strict_types=1);

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Data\User;
use Plume\Data\XList;
use Plume\Facades\X;

beforeEach(function (): void {
    config(['x.bearer_token' => 'test-token']);
    X::fake();
});

function fakeOwnedLists(): PaginatedResult
{
    return new PaginatedResult(
        data: [
            new XList(id: '100', name: 'My List', description: 'A test list'),
            new XList(id: '101', name: 'Private List', private: true),
        ],
        resultCount: 2,
    );
}

// ── ListsCommand ─────────────────────────────────────────────

it('lists owned lists', function (): void {
    X::fake()->shouldReturn('ownedLists', fakeOwnedLists());

    $this->artisan('plume:lists')
        ->assertSuccessful()
        ->expectsOutputToContain('2 item(s) found');
});

it('shows empty message when no lists', function (): void {
    $this->artisan('plume:lists')
        ->assertSuccessful()
        ->expectsOutputToContain('No lists found');
});

it('lists owned lists as json', function (): void {
    X::fake()->shouldReturn('ownedLists', fakeOwnedLists());

    $this->artisan('plume:lists', ['--format' => 'json'])
        ->assertSuccessful();
});

// ── CreateListCommand ────────────────────────────────────────

it('creates a list', function (): void {
    $this->artisan('plume:lists:create', ['name' => 'New List'])
        ->assertSuccessful()
        ->expectsOutputToContain('List created successfully');
});

it('creates a list with description and private flag', function (): void {
    $this->artisan('plume:lists:create', [
        'name' => 'Private List',
        '--description' => 'Secret stuff',
        '--private' => true,
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('List created successfully');
});

it('creates a list as json', function (): void {
    $this->artisan('plume:lists:create', ['name' => 'JSON List', '--format' => 'json'])
        ->assertSuccessful();
});

// ── GetListCommand ───────────────────────────────────────────

it('shows list details', function (): void {
    $this->artisan('plume:lists:get', ['id' => '100'])
        ->assertSuccessful();
});

it('shows list details as json', function (): void {
    $this->artisan('plume:lists:get', ['id' => '100', '--format' => 'json'])
        ->assertSuccessful();
});

// ── UpdateListCommand ────────────────────────────────────────

it('updates a list name', function (): void {
    $this->artisan('plume:lists:update', ['id' => '100', '--name' => 'Updated Name'])
        ->assertSuccessful()
        ->expectsOutputToContain('List updated successfully');
});

it('fails when no update options provided', function (): void {
    $this->artisan('plume:lists:update', ['id' => '100'])
        ->assertFailed();
});

// ── DeleteListCommand ────────────────────────────────────────

it('deletes a list with force', function (): void {
    $this->artisan('plume:lists:delete', ['id' => '100', '--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('List deleted successfully');
});

// ── AddMemberCommand ─────────────────────────────────────────

it('adds a member to a list', function (): void {
    $this->artisan('plume:lists:add-member', ['list-id' => '100', 'user-id' => '42'])
        ->assertSuccessful()
        ->expectsOutputToContain('Member added successfully');
});

// ── RemoveMemberCommand ──────────────────────────────────────

it('removes a member from a list with force', function (): void {
    $this->artisan('plume:lists:remove-member', ['list-id' => '100', 'user-id' => '42', '--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Member removed successfully');
});

// ── ListMembersCommand ───────────────────────────────────────

it('lists members of a list', function (): void {
    $listUsers = new PaginatedResult(
        data: [
            new User(id: '1', name: 'Alice', username: 'alice'),
        ],
        resultCount: 1,
    );
    X::fake()->shouldReturn('listMembers', $listUsers);

    $this->artisan('plume:lists:members', ['id' => '100'])
        ->assertSuccessful()
        ->expectsOutputToContain('1 item(s) found');
});

it('shows empty message when list has no members', function (): void {
    $this->artisan('plume:lists:members', ['id' => '100'])
        ->assertSuccessful()
        ->expectsOutputToContain('No members found');
});

// ── ListTweetsCommand ────────────────────────────────────────

it('lists tweets from a list', function (): void {
    $listTweets = new PaginatedResult(
        data: [
            new Post(id: '50', text: 'List tweet', createdAt: '2025-01-01T00:00:00Z'),
        ],
        resultCount: 1,
    );
    X::fake()->shouldReturn('listTweets', $listTweets);

    $this->artisan('plume:lists:tweets', ['id' => '100'])
        ->assertSuccessful()
        ->expectsOutputToContain('1 item(s) found');
});

it('shows empty message when list has no tweets', function (): void {
    $this->artisan('plume:lists:tweets', ['id' => '100'])
        ->assertSuccessful()
        ->expectsOutputToContain('No tweets found');
});

// ── FollowListCommand ────────────────────────────────────────

it('follows a list', function (): void {
    $this->artisan('plume:lists:follow', ['id' => '100'])
        ->assertSuccessful()
        ->expectsOutputToContain('List followed successfully');
});

// ── UnfollowListCommand ──────────────────────────────────────

it('unfollows a list', function (): void {
    $this->artisan('plume:lists:unfollow', ['id' => '100'])
        ->assertSuccessful()
        ->expectsOutputToContain('List unfollowed successfully');
});

// ── PinListCommand ───────────────────────────────────────────

it('pins a list', function (): void {
    $this->artisan('plume:lists:pin', ['id' => '100'])
        ->assertSuccessful()
        ->expectsOutputToContain('List pinned successfully');
});

// ── UnpinListCommand ─────────────────────────────────────────

it('unpins a list', function (): void {
    $this->artisan('plume:lists:unpin', ['id' => '100'])
        ->assertSuccessful()
        ->expectsOutputToContain('List unpinned successfully');
});
