<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Data\Includes;
use Plume\Enums\Expansion;
use Plume\Enums\MediaField;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function fieldClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('sends tweet.fields in query', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123*' => Http::response([
            'data' => ['id' => '123', 'text' => 'Hello', 'author_id' => '456', 'created_at' => '2024-01-01T00:00:00Z'],
        ]),
    ]);

    fieldClient()->getPost('123', tweetFields: [TweetField::AuthorId, TweetField::CreatedAt]);

    Http::assertSent(function ($request) {
        $url = $request->url();

        return str_contains($url, 'tweet.fields=author_id%2Ccreated_at');
    });
});

it('sends expansions in query', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123*' => Http::response([
            'data' => ['id' => '123', 'text' => 'Hello', 'author_id' => '456'],
            'includes' => [
                'users' => [
                    ['id' => '456', 'name' => 'Author', 'username' => 'author'],
                ],
            ],
        ]),
    ]);

    $post = fieldClient()->getPost('123',
        tweetFields: [TweetField::AuthorId],
        expansions: [Expansion::AuthorId],
    );

    expect($post->includes)->toBeInstanceOf(Includes::class)
        ->and($post->includes->users)->toHaveCount(1)
        ->and($post->includes->users[0]->username)->toBe('author');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'expansions=author_id'));
});

it('sends user.fields in query', function (): void {
    Http::fake([
        'api.x.com/2/users/123*' => Http::response([
            'data' => [
                'id' => '123',
                'name' => 'Test',
                'username' => 'test',
                'description' => 'A description',
                'profile_image_url' => 'https://example.com/img.jpg',
            ],
        ]),
    ]);

    fieldClient()->getUser('123', userFields: [UserField::Description, UserField::ProfileImageUrl]);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'user.fields=description%2Cprofile_image_url'));
});

it('sends media.fields in query', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123*' => Http::response([
            'data' => ['id' => '123', 'text' => 'Photo tweet'],
            'includes' => [
                'media' => [
                    ['media_key' => 'mk1', 'type' => 'photo', 'url' => 'https://example.com/photo.jpg'],
                ],
            ],
        ]),
    ]);

    $post = fieldClient()->getPost('123',
        expansions: [Expansion::AttachmentsMediaKeys],
        mediaFields: [MediaField::Url, MediaField::AltText],
    );

    expect($post->includes->media)->toHaveCount(1)
        ->and($post->includes->media[0]->url)->toBe('https://example.com/photo.jpg');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'media.fields=url%2Calt_text'));
});

it('combines multiple field types in a single request', function (): void {
    Http::fake([
        'api.x.com/2/tweets/123*' => Http::response([
            'data' => ['id' => '123', 'text' => 'Complex'],
        ]),
    ]);

    fieldClient()->getPost('123',
        tweetFields: [TweetField::AuthorId],
        expansions: [Expansion::AuthorId],
        userFields: [UserField::Username],
        mediaFields: [MediaField::Url],
    );

    Http::assertSent(function ($request) {
        $url = $request->url();

        return str_contains($url, 'tweet.fields=')
            && str_contains($url, 'expansions=')
            && str_contains($url, 'user.fields=')
            && str_contains($url, 'media.fields=');
    });
});
