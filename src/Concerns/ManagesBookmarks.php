<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Enums\Expansion;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait ManagesBookmarks
{
    public function bookmark(string $userId, string $tweetId): void
    {
        $this->http->post("/2/users/{$userId}/bookmarks", [
            'tweet_id' => $tweetId,
        ]);
    }

    public function removeBookmark(string $userId, string $tweetId): void
    {
        $this->http->delete("/2/users/{$userId}/bookmarks/{$tweetId}");
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function bookmarks(
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

        $response = $this->http->get("/2/users/{$userId}/bookmarks", $query);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->bookmarks(
            $userId, $maxResults, $token, $tweetFields, $expansions, $userFields,
        ));
    }
}
