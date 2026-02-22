<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\User;
use Plume\Enums\UserField;

trait ManagesMutes
{
    public function mute(string $userId, string $targetUserId): void
    {
        $this->http->post("/2/users/{$userId}/muting", [
            'target_user_id' => $targetUserId,
        ]);
    }

    public function unmute(string $userId, string $targetUserId): void
    {
        $this->http->delete("/2/users/{$userId}/muting/{$targetUserId}");
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function mutedUsers(
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

        $response = $this->http->get("/2/users/{$userId}/muting", $query);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->mutedUsers(
            $userId, $maxResults, $token, $userFields,
        ));
    }
}
