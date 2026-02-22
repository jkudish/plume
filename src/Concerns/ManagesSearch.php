<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Enums\Expansion;
use Plume\Enums\Granularity;
use Plume\Enums\SortOrder;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait ManagesSearch
{
    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function searchRecent(
        string $query,
        int $maxResults = 10,
        ?string $nextToken = null,
        ?SortOrder $sortOrder = null,
        ?string $sinceId = null,
        ?string $untilId = null,
        ?string $startTime = null,
        ?string $endTime = null,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
    ): PaginatedResult {
        $params = array_merge(
            ['query' => $query, 'max_results' => $maxResults],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields),
        );

        $this->addSearchParams($params, $nextToken, $sortOrder, $sinceId, $untilId, $startTime, $endTime);

        $response = $this->http->get('/2/tweets/search/recent', $params);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->searchRecent(
            $query, $maxResults, $token, $sortOrder, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields,
        ));
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function searchAll(
        string $query,
        int $maxResults = 10,
        ?string $nextToken = null,
        ?SortOrder $sortOrder = null,
        ?string $sinceId = null,
        ?string $untilId = null,
        ?string $startTime = null,
        ?string $endTime = null,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
    ): PaginatedResult {
        $params = array_merge(
            ['query' => $query, 'max_results' => $maxResults],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields),
        );

        $this->addSearchParams($params, $nextToken, $sortOrder, $sinceId, $untilId, $startTime, $endTime);

        $response = $this->http->get('/2/tweets/search/all', $params);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->searchAll(
            $query, $maxResults, $token, $sortOrder, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function countRecent(
        string $query,
        ?Granularity $granularity = null,
        ?string $sinceId = null,
        ?string $untilId = null,
        ?string $startTime = null,
        ?string $endTime = null,
    ): array {
        $params = ['query' => $query];
        $this->addCountParams($params, $granularity, $sinceId, $untilId, $startTime, $endTime);

        return $this->http->get('/2/tweets/counts/recent', $params);
    }

    /**
     * @return array<string, mixed>
     */
    public function countAll(
        string $query,
        ?Granularity $granularity = null,
        ?string $sinceId = null,
        ?string $untilId = null,
        ?string $startTime = null,
        ?string $endTime = null,
    ): array {
        $params = ['query' => $query];
        $this->addCountParams($params, $granularity, $sinceId, $untilId, $startTime, $endTime);

        return $this->http->get('/2/tweets/counts/all', $params);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function addSearchParams(
        array &$params,
        ?string $nextToken,
        ?SortOrder $sortOrder,
        ?string $sinceId,
        ?string $untilId,
        ?string $startTime,
        ?string $endTime,
    ): void {
        if ($nextToken !== null) {
            $params['next_token'] = $nextToken;
        }
        if ($sortOrder !== null) {
            $params['sort_order'] = $sortOrder->value;
        }
        if ($sinceId !== null) {
            $params['since_id'] = $sinceId;
        }
        if ($untilId !== null) {
            $params['until_id'] = $untilId;
        }
        if ($startTime !== null) {
            $params['start_time'] = $startTime;
        }
        if ($endTime !== null) {
            $params['end_time'] = $endTime;
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function addCountParams(
        array &$params,
        ?Granularity $granularity,
        ?string $sinceId,
        ?string $untilId,
        ?string $startTime,
        ?string $endTime,
    ): void {
        if ($granularity !== null) {
            $params['granularity'] = $granularity->value;
        }
        if ($sinceId !== null) {
            $params['since_id'] = $sinceId;
        }
        if ($untilId !== null) {
            $params['until_id'] = $untilId;
        }
        if ($startTime !== null) {
            $params['start_time'] = $startTime;
        }
        if ($endTime !== null) {
            $params['end_time'] = $endTime;
        }
    }
}
