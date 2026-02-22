<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function blocksClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('blocks a user', function (): void {
    Http::fake([
        'api.x.com/2/users/123/blocking' => Http::response(['data' => ['blocking' => true]]),
    ]);

    blocksClient()->block('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['target_user_id'] === '456');
});

it('unblocks a user', function (): void {
    Http::fake([
        'api.x.com/2/users/123/blocking/456' => Http::response(['data' => ['blocking' => false]]),
    ]);

    blocksClient()->unblock('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('lists blocked users', function (): void {
    Http::fake([
        'api.x.com/2/users/123/blocking*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'Blocked', 'username' => 'blocked'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = blocksClient()->blockedUsers('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->username)->toBe('blocked');
});
