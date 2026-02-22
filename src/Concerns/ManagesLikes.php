<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Data\User;
use Plume\Enums\Expansion;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait ManagesLikes
{
    public function like(string $userId, string $tweetId): void
    {
        $this->http->post("/2/users/{$userId}/likes", [
            'tweet_id' => $tweetId,
        ]);
    }

    public function unlike(string $userId, string $tweetId): void
    {
        $this->http->delete("/2/users/{$userId}/likes/{$tweetId}");
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function likingUsers(
        string $tweetId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult {
        $query = array_merge(
            ['max_results' => $maxResults],
            $this->buildFieldQuery(userFields: $userFields),
        );

        if ($paginationToken !== null) {
            $query['pagination_token'] = $paginationToken;
        }

        $response = $this->http->get("/2/tweets/{$tweetId}/liking_users", $query);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->likingUsers(
            $tweetId, $maxResults, $token, $userFields,
        ));
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function likedTweets(
        string $userId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
    ): PaginatedResult {
        $query = array_merge(
            ['max_results' => $maxResults],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields),
        );

        if ($paginationToken !== null) {
            $query['pagination_token'] = $paginationToken;
        }

        $response = $this->http->get("/2/users/{$userId}/liked_tweets", $query);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->likedTweets(
            $userId, $maxResults, $token, $tweetFields, $expansions, $userFields,
        ));
    }
}
