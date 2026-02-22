<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Enums\Exclude;
use Plume\Enums\Expansion;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait ManagesTimelines
{
    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<Exclude>  $exclude
     * @return PaginatedResult<Post>
     */
    public function userTimeline(
        string $userId,
        int $maxResults = 10,
        ?string $paginationToken = null,
        ?string $sinceId = null,
        ?string $untilId = null,
        ?string $startTime = null,
        ?string $endTime = null,
        array $exclude = [],
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
    ): PaginatedResult {
        $query = array_merge(
            ['max_results' => $maxResults],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields),
        );

        $this->addPaginationParams($query, $paginationToken, $sinceId, $untilId, $startTime, $endTime);

        if ($exclude !== []) {
            $query['exclude'] = implode(',', array_map(fn (Exclude $e): string => $e->value, $exclude));
        }

        $response = $this->http->get("/2/users/{$userId}/tweets", $query);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->userTimeline(
            $userId, $maxResults, $token, $sinceId, $untilId, $startTime, $endTime, $exclude, $tweetFields, $expansions, $userFields,
        ));
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function mentionsTimeline(
        string $userId,
        int $maxResults = 10,
        ?string $paginationToken = null,
        ?string $sinceId = null,
        ?string $untilId = null,
        ?string $startTime = null,
        ?string $endTime = null,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
    ): PaginatedResult {
        $query = array_merge(
            ['max_results' => $maxResults],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields),
        );

        $this->addPaginationParams($query, $paginationToken, $sinceId, $untilId, $startTime, $endTime);

        $response = $this->http->get("/2/users/{$userId}/mentions", $query);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->mentionsTimeline(
            $userId, $maxResults, $token, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields,
        ));
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<Exclude>  $exclude
     * @return PaginatedResult<Post>
     */
    public function homeTimeline(
        string $userId,
        int $maxResults = 10,
        ?string $paginationToken = null,
        ?string $sinceId = null,
        ?string $untilId = null,
        ?string $startTime = null,
        ?string $endTime = null,
        array $exclude = [],
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
    ): PaginatedResult {
        $query = array_merge(
            ['max_results' => $maxResults],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields),
        );

        $this->addPaginationParams($query, $paginationToken, $sinceId, $untilId, $startTime, $endTime);

        if ($exclude !== []) {
            $query['exclude'] = implode(',', array_map(fn (Exclude $e): string => $e->value, $exclude));
        }

        $response = $this->http->get("/2/users/{$userId}/timelines/reverse_chronological", $query);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->homeTimeline(
            $userId, $maxResults, $token, $sinceId, $untilId, $startTime, $endTime, $exclude, $tweetFields, $expansions, $userFields,
        ));
    }

    /**
     * @param  array<string, mixed>  $query
     */
    protected function addPaginationParams(
        array &$query,
        ?string $paginationToken,
        ?string $sinceId,
        ?string $untilId,
        ?string $startTime,
        ?string $endTime,
    ): void {
        if ($paginationToken !== null) {
            $query['pagination_token'] = $paginationToken;
        }
        if ($sinceId !== null) {
            $query['since_id'] = $sinceId;
        }
        if ($untilId !== null) {
            $query['until_id'] = $untilId;
        }
        if ($startTime !== null) {
            $query['start_time'] = $startTime;
        }
        if ($endTime !== null) {
            $query['end_time'] = $endTime;
        }
    }
}
