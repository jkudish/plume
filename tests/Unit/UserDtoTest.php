<?php

declare(strict_types=1);

use Plume\Data\User;
use Plume\Data\UserMetrics;

it('constructs with required fields', function (): void {
    $user = new User(id: '123', name: 'Test User', username: 'testuser');

    expect($user->id)->toBe('123')
        ->and($user->name)->toBe('Test User')
        ->and($user->username)->toBe('testuser')
        ->and($user->description)->toBeNull()
        ->and($user->publicMetrics)->toBeNull();
});

it('constructs with all fields', function (): void {
    $metrics = new UserMetrics(
        followersCount: 1000,
        followingCount: 500,
        tweetCount: 5000,
        listedCount: 50,
    );

    $user = new User(
        id: '456',
        name: 'Full User',
        username: 'fulluser',
        description: 'A full user profile',
        location: 'San Francisco',
        url: 'https://example.com',
        profileImageUrl: 'https://pbs.twimg.com/profile_images/123.jpg',
        profileBannerUrl: 'https://pbs.twimg.com/profile_banners/123.jpg',
        createdAt: '2020-01-01T00:00:00Z',
        pinnedTweetId: '789',
        protected: false,
        verified: true,
        verifiedType: 'blue',
        publicMetrics: $metrics,
    );

    expect($user->id)->toBe('456')
        ->and($user->verified)->toBeTrue()
        ->and($user->publicMetrics->followersCount)->toBe(1000);
});

it('throws LogicException when calling action without provider', function (): void {
    $user = new User(id: '123', name: 'Test', username: 'test');
    $user->follow('456');
})->throws(LogicException::class, 'User requires an XApiProvider');
