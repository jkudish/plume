<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function mutesClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('mutes a user', function (): void {
    Http::fake([
        'api.x.com/2/users/123/muting' => Http::response(['data' => ['muting' => true]]),
    ]);

    mutesClient()->mute('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['target_user_id'] === '456');
});

it('unmutes a user', function (): void {
    Http::fake([
        'api.x.com/2/users/123/muting/456' => Http::response(['data' => ['muting' => false]]),
    ]);

    mutesClient()->unmute('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('lists muted users', function (): void {
    Http::fake([
        'api.x.com/2/users/123/muting*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'Muted', 'username' => 'muted'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = mutesClient()->mutedUsers('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->username)->toBe('muted');
});
