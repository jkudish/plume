<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Data\User;
use Plume\Enums\UserField;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function usersClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('gets a user by id', function (): void {
    Http::fake([
        'api.x.com/2/users/123*' => Http::response([
            'data' => ['id' => '123', 'name' => 'Test User', 'username' => 'testuser'],
        ]),
    ]);

    $user = usersClient()->getUser('123');

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->id)->toBe('123')
        ->and($user->name)->toBe('Test User')
        ->and($user->username)->toBe('testuser');
});

it('gets a user with fields', function (): void {
    Http::fake([
        'api.x.com/2/users/123*' => Http::response([
            'data' => [
                'id' => '123',
                'name' => 'Test',
                'username' => 'test',
                'public_metrics' => [
                    'followers_count' => 1000,
                    'following_count' => 500,
                    'tweet_count' => 5000,
                    'listed_count' => 50,
                ],
            ],
        ]),
    ]);

    $user = usersClient()->getUser('123', userFields: [UserField::PublicMetrics]);

    expect($user->publicMetrics)->not->toBeNull()
        ->and($user->publicMetrics->followersCount)->toBe(1000);
});

it('gets multiple users', function (): void {
    Http::fake([
        'api.x.com/2/users?*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'User 1', 'username' => 'user1'],
                ['id' => '2', 'name' => 'User 2', 'username' => 'user2'],
            ],
        ]),
    ]);

    $users = usersClient()->getUsers(['1', '2']);

    expect($users)->toHaveCount(2);
});

it('gets a user by username', function (): void {
    Http::fake([
        'api.x.com/2/users/by/username/testuser*' => Http::response([
            'data' => ['id' => '123', 'name' => 'Test', 'username' => 'testuser'],
        ]),
    ]);

    $user = usersClient()->getUserByUsername('testuser');

    expect($user->username)->toBe('testuser');
});

it('gets multiple users by usernames', function (): void {
    Http::fake([
        'api.x.com/2/users/by?*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'User 1', 'username' => 'user1'],
                ['id' => '2', 'name' => 'User 2', 'username' => 'user2'],
            ],
        ]),
    ]);

    $users = usersClient()->getUsersByUsernames(['user1', 'user2']);

    expect($users)->toHaveCount(2);
});

it('gets the authenticated user', function (): void {
    Http::fake([
        'api.x.com/2/users/me*' => Http::response([
            'data' => ['id' => '999', 'name' => 'Me', 'username' => 'myself'],
        ]),
    ]);

    $user = usersClient()->me();

    expect($user->id)->toBe('999')
        ->and($user->username)->toBe('myself');
});

it('searches users', function (): void {
    Http::fake([
        'api.x.com/2/users/search*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'Laravel Dev', 'username' => 'laraveldev'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = usersClient()->searchUsers('laravel');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->username)->toBe('laraveldev');
});
