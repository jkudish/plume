<?php

declare(strict_types=1);

it('fails gracefully when bearer token is not set', function (): void {
    config(['x.bearer_token' => null]);

    $this->artisan('plume:me')
        ->assertFailed()
        ->expectsOutputToContain('No X API bearer token configured');
});

it('fails gracefully when bearer token is empty string', function (): void {
    config(['x.bearer_token' => '']);

    $this->artisan('plume:me')
        ->assertFailed()
        ->expectsOutputToContain('No X API bearer token configured');
});

it('fails gracefully for post command without bearer token', function (): void {
    config(['x.bearer_token' => null]);

    $this->artisan('plume:post', ['--text' => 'Hello'])
        ->assertFailed()
        ->expectsOutputToContain('No X API bearer token configured');
});

it('fails gracefully for search command without bearer token', function (): void {
    config(['x.bearer_token' => null]);

    $this->artisan('plume:search', ['query' => 'test'])
        ->assertFailed()
        ->expectsOutputToContain('No X API bearer token configured');
});

it('fails gracefully for like command without bearer token', function (): void {
    config(['x.bearer_token' => null]);

    $this->artisan('plume:like', ['id' => '123'])
        ->assertFailed()
        ->expectsOutputToContain('No X API bearer token configured');
});

it('fails gracefully for lists command without bearer token', function (): void {
    config(['x.bearer_token' => null]);

    $this->artisan('plume:lists:get', ['id' => '100'])
        ->assertFailed()
        ->expectsOutputToContain('No X API bearer token configured');
});
