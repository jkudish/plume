<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\User;
use Plume\Enums\UserField;

trait ManagesBlocks
{
    public function block(string $userId, string $targetUserId): void
    {
        $this->http->post("/2/users/{$userId}/blocking", [
            'target_user_id' => $targetUserId,
        ]);
    }

    public function unblock(string $userId, string $targetUserId): void
    {
        $this->http->delete("/2/users/{$userId}/blocking/{$targetUserId}");
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function blockedUsers(
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

        $response = $this->http->get("/2/users/{$userId}/blocking", $query);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->blockedUsers(
            $userId, $maxResults, $token, $userFields,
        ));
    }
}
