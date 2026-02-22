<?php

declare(strict_types=1);

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Facades\X;

beforeEach(function (): void {
    config(['x.bearer_token' => 'test-token']);
    X::fake();
});

function fakePosts(): PaginatedResult
{
    return new PaginatedResult(
        data: [
            new Post(id: '1', text: 'First post', createdAt: '2025-01-01T00:00:00Z'),
            new Post(id: '2', text: 'Second post', createdAt: '2025-01-02T00:00:00Z'),
        ],
        resultCount: 2,
    );
}

// ── HomeCommand ──────────────────────────────────────────────

it('shows the home timeline', function (): void {
    X::fake()->shouldReturn('homeTimeline', fakePosts());

    $this->artisan('plume:home')
        ->assertSuccessful()
        ->expectsOutputToContain('2 post(s) found');
});

it('shows empty message when home timeline is empty', function (): void {
    $this->artisan('plume:home')
        ->assertSuccessful()
        ->expectsOutputToContain('No posts found');
});

it('shows home timeline as json', function (): void {
    X::fake()->shouldReturn('homeTimeline', fakePosts());

    $this->artisan('plume:home', ['--format' => 'json'])
        ->assertSuccessful();
});

// ── TimelineCommand ──────────────────────────────────────────

it('shows the user timeline', function (): void {
    X::fake()->shouldReturn('userTimeline', fakePosts());

    $this->artisan('plume:timeline')
        ->assertSuccessful()
        ->expectsOutputToContain('2 post(s) found');
});

it('shows empty message when timeline is empty', function (): void {
    $this->artisan('plume:timeline')
        ->assertSuccessful()
        ->expectsOutputToContain('No posts found');
});

it('shows timeline as json', function (): void {
    X::fake()->shouldReturn('userTimeline', fakePosts());

    $this->artisan('plume:timeline', ['--format' => 'json'])
        ->assertSuccessful();
});

// ── MentionsCommand ──────────────────────────────────────────

it('shows mentions', function (): void {
    X::fake()->shouldReturn('mentionsTimeline', fakePosts());

    $this->artisan('plume:mentions')
        ->assertSuccessful()
        ->expectsOutputToContain('2 mention(s) found');
});

it('shows empty message when no mentions', function (): void {
    $this->artisan('plume:mentions')
        ->assertSuccessful()
        ->expectsOutputToContain('No mentions found');
});

it('shows mentions as json', function (): void {
    X::fake()->shouldReturn('mentionsTimeline', fakePosts());

    $this->artisan('plume:mentions', ['--format' => 'json'])
        ->assertSuccessful();
});

// ── SearchCommand ────────────────────────────────────────────

it('searches recent posts', function (): void {
    X::fake()->shouldReturn('searchRecent', fakePosts());

    $this->artisan('plume:search', ['query' => 'laravel'])
        ->assertSuccessful()
        ->expectsOutputToContain('2 post(s) found');
});

it('shows empty message when no search results', function (): void {
    $this->artisan('plume:search', ['query' => 'xyznotfound'])
        ->assertSuccessful()
        ->expectsOutputToContain('No posts found');
});

it('accepts sort option for search', function (): void {
    X::fake()->shouldReturn('searchRecent', fakePosts());

    $this->artisan('plume:search', ['query' => 'laravel', '--sort' => 'recency'])
        ->assertSuccessful();
});

it('fails with invalid sort option', function (): void {
    $this->artisan('plume:search', ['query' => 'laravel', '--sort' => 'invalid'])
        ->assertFailed();
});

it('shows search results as json', function (): void {
    X::fake()->shouldReturn('searchRecent', fakePosts());

    $this->artisan('plume:search', ['query' => 'laravel', '--format' => 'json'])
        ->assertSuccessful();
});
