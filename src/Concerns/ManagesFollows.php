<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\User;
use Plume\Enums\UserField;

trait ManagesFollows
{
    public function follow(string $userId, string $targetUserId): void
    {
        $this->http->post("/2/users/{$userId}/following", [
            'target_user_id' => $targetUserId,
        ]);
    }

    public function unfollow(string $userId, string $targetUserId): void
    {
        $this->http->delete("/2/users/{$userId}/following/{$targetUserId}");
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function followers(
        string $userId,
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

        $response = $this->http->get("/2/users/{$userId}/followers", $query);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->followers(
            $userId, $maxResults, $token, $userFields,
        ));
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function following(
        string $userId,
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

        $response = $this->http->get("/2/users/{$userId}/following", $query);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->following(
            $userId, $maxResults, $token, $userFields,
        ));
    }
}
