<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Plume\Data\PaginatedResult;
use Plume\Http\XHttpClient;
use Plume\XApiClient;

function paginationClient(): XApiClient
{
    return new XApiClient(new XHttpClient(
        baseUrl: 'https://api.x.com',
        timeout: 30,
        bearerToken: 'test-bearer',
    ));
}

it('returns pagination metadata', function (): void {
    Http::fake([
        'api.x.com/2/tweets/search/recent*' => Http::response([
            'data' => [
                ['id' => '1', 'text' => 'Post 1'],
                ['id' => '2', 'text' => 'Post 2'],
            ],
            'meta' => [
                'result_count' => 2,
                'next_token' => 'next_abc123',
                'previous_token' => 'prev_xyz789',
            ],
        ]),
    ]);

    $result = paginationClient()->searchRecent('test');

    expect($result)->toBeInstanceOf(PaginatedResult::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->resultCount)->toBe(2)
        ->and($result->nextToken)->toBe('next_abc123')
        ->and($result->previousToken)->toBe('prev_xyz789')
        ->and($result->hasNextPage())->toBeTrue();
});

it('handles empty results', function (): void {
    Http::fake([
        'api.x.com/2/tweets/search/recent*' => Http::response([
            'meta' => ['result_count' => 0],
        ]),
    ]);

    $result = paginationClient()->searchRecent('no-results');

    expect($result->data)->toBeEmpty()
        ->and($result->hasNextPage())->toBeFalse()
        ->and($result->nextPage())->toBeNull();
});

it('paginates to next page', function (): void {
    $callCount = 0;

    Http::fake([
        'api.x.com/2/tweets/search/recent*' => function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return Http::response([
                    'data' => [['id' => '1', 'text' => 'Page 1']],
                    'meta' => ['result_count' => 1, 'next_token' => 'page2token'],
                ]);
            }

            return Http::response([
                'data' => [['id' => '2', 'text' => 'Page 2']],
                'meta' => ['result_count' => 1],
            ]);
        },
    ]);

    $page1 = paginationClient()->searchRecent('test');
    $page2 = $page1->nextPage();

    expect($page1->data[0]->text)->toBe('Page 1')
        ->and($page2)->not->toBeNull()
        ->and($page2->data[0]->text)->toBe('Page 2')
        ->and($page2->hasNextPage())->toBeFalse();
});

it('returns null on nextPage when no more pages', function (): void {
    Http::fake([
        'api.x.com/2/tweets/search/recent*' => Http::response([
            'data' => [['id' => '1', 'text' => 'Only page']],
            'meta' => ['result_count' => 1],
        ]),
    ]);

    $result = paginationClient()->searchRecent('test');

    expect($result->nextPage())->toBeNull();
});
