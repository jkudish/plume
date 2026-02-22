<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Data\XList;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function listsClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('gets a list by id', function (): void {
    Http::fake([
        'api.x.com/2/lists/123*' => Http::response([
            'data' => ['id' => '123', 'name' => 'My List'],
        ]),
    ]);

    $list = listsClient()->getList('123');

    expect($list)->toBeInstanceOf(XList::class)
        ->and($list->id)->toBe('123')
        ->and($list->name)->toBe('My List');
});

it('creates a list', function (): void {
    Http::fake([
        'api.x.com/2/lists' => Http::response([
            'data' => ['id' => '456', 'name' => 'New List'],
        ]),
    ]);

    $list = listsClient()->createList('New List', ['description' => 'A test list']);

    expect($list->id)->toBe('456');

    Http::assertSent(fn ($r) => $r['name'] === 'New List' && $r['description'] === 'A test list');
});

it('updates a list', function (): void {
    Http::fake([
        'api.x.com/2/lists/123' => Http::response([
            'data' => ['id' => '123', 'name' => 'Updated'],
        ]),
    ]);

    $list = listsClient()->updateList('123', ['name' => 'Updated']);

    expect($list->name)->toBe('Updated');
});

it('deletes a list', function (): void {
    Http::fake([
        'api.x.com/2/lists/123' => Http::response(['data' => ['deleted' => true]]),
    ]);

    listsClient()->deleteList('123');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('adds a member to a list', function (): void {
    Http::fake([
        'api.x.com/2/lists/123/members' => Http::response(['data' => ['is_member' => true]]),
    ]);

    listsClient()->addListMember('123', '456');

    Http::assertSent(fn ($r) => $r['user_id'] === '456');
});

it('removes a member from a list', function (): void {
    Http::fake([
        'api.x.com/2/lists/123/members/456' => Http::response(['data' => ['is_member' => false]]),
    ]);

    listsClient()->removeListMember('123', '456');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('gets list members', function (): void {
    Http::fake([
        'api.x.com/2/lists/123/members*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'Member', 'username' => 'member'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = listsClient()->listMembers('123');

    expect($result->data)->toHaveCount(1);
});

it('gets list tweets', function (): void {
    Http::fake([
        'api.x.com/2/lists/123/tweets*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'List tweet'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = listsClient()->listTweets('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0]->text)->toBe('List tweet');
});

it('follows and unfollows a list', function (): void {
    Http::fake([
        'api.x.com/2/users/123/followed_lists' => Http::response(['data' => ['following' => true]]),
        'api.x.com/2/users/123/followed_lists/456' => Http::response(['data' => ['following' => false]]),
    ]);

    listsClient()->followList('123', '456');
    listsClient()->unfollowList('123', '456');

    Http::assertSentCount(2);
});

it('pins and unpins a list', function (): void {
    Http::fake([
        'api.x.com/2/users/123/pinned_lists' => Http::response(['data' => ['pinned' => true]]),
        'api.x.com/2/users/123/pinned_lists/456' => Http::response(['data' => ['pinned' => false]]),
    ]);

    listsClient()->pinList('123', '456');
    listsClient()->unpinList('123', '456');

    Http::assertSentCount(2);
});

it('gets owned lists', function (): void {
    Http::fake([
        'api.x.com/2/users/123/owned_lists*' => Http::response([
            'data' => [
                ['id' => '1', 'name' => 'My List'],
            ],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = listsClient()->ownedLists('123');

    expect($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(XList::class);
});
