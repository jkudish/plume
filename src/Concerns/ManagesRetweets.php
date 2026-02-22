<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Data\User;
use Plume\Enums\Expansion;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait ManagesRetweets
{
    public function retweet(string $userId, string $tweetId): void
    {
        $this->http->post("/2/users/{$userId}/retweets", [
            'tweet_id' => $tweetId,
        ]);
    }

    public function undoRetweet(string $userId, string $tweetId): void
    {
        $this->http->delete("/2/users/{$userId}/retweets/{$tweetId}");
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function retweetedBy(
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

        $response = $this->http->get("/2/tweets/{$tweetId}/retweeted_by", $query);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->retweetedBy(
            $tweetId, $maxResults, $token, $userFields,
        ));
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function quoteTweets(
        string $tweetId,
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

        $response = $this->http->get("/2/tweets/{$tweetId}/quote_tweets", $query);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->quoteTweets(
            $tweetId, $maxResults, $token, $tweetFields, $expansions, $userFields,
        ));
    }
}
