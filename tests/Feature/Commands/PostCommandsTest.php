<?php

declare(strict_types=1);

use Plume\Facades\X;

beforeEach(function (): void {
    config(['x.bearer_token' => 'test-token']);
    X::fake();
});

// ── MeCommand ────────────────────────────────────────────────

it('shows the authenticated user profile', function (): void {
    $this->artisan('plume:me')
        ->assertSuccessful()
        ->expectsOutputToContain('Test User')
        ->expectsOutputToContain('@testuser')
        ->expectsOutputToContain('999');
});

it('shows the authenticated user profile as json', function (): void {
    $this->artisan('plume:me', ['--format' => 'json'])
        ->assertSuccessful();
});

// ── UserCommand ──────────────────────────────────────────────

it('looks up a user by id', function (): void {
    $this->artisan('plume:user', ['id' => '42'])
        ->assertSuccessful();
});

it('looks up a user by username', function (): void {
    $this->artisan('plume:user', ['--username' => 'johndoe'])
        ->assertSuccessful();
});

it('fails when neither id nor username is provided', function (): void {
    $this->artisan('plume:user')
        ->assertFailed();
});

it('shows user as json', function (): void {
    $this->artisan('plume:user', ['id' => '42', '--format' => 'json'])
        ->assertSuccessful();
});

// ── PostCommand ──────────────────────────────────────────────

it('creates a post with text', function (): void {
    $this->artisan('plume:post', ['--text' => 'Hello world'])
        ->assertSuccessful()
        ->expectsOutputToContain('Post created successfully');
});

it('fails when text is not provided', function (): void {
    $this->artisan('plume:post')
        ->assertFailed();
});

it('creates a post as json output', function (): void {
    $this->artisan('plume:post', ['--text' => 'Hello world', '--format' => 'json'])
        ->assertSuccessful();
});

// ── GetPostCommand ───────────────────────────────────────────

it('shows a post by id in table format', function (): void {
    $this->artisan('plume:get-post', ['id' => '123'])
        ->assertSuccessful();
});

it('shows a post by id as json', function (): void {
    $this->artisan('plume:get-post', ['id' => '123', '--format' => 'json'])
        ->assertSuccessful();
});

// ── DeletePostCommand ────────────────────────────────────────

it('deletes a post with force flag', function (): void {
    $this->artisan('plume:delete-post', ['id' => '123', '--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Post deleted successfully');
});
