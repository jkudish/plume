<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Data\User;
use Plume\Data\XList;
use Plume\Enums\Expansion;
use Plume\Enums\ListField;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait ManagesLists
{
    /**
     * @param  list<ListField>  $listFields
     */
    public function getList(
        string $id,
        array $listFields = [],
    ): XList {
        $query = $this->buildFieldQuery(listFields: $listFields);
        $response = $this->http->get("/2/lists/{$id}", $query);

        /** @var array<string, mixed> $data */
        $data = $response['data'];

        return $this->mapList($data);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function createList(string $name, array $options = []): XList
    {
        $response = $this->http->post('/2/lists', array_merge(['name' => $name], $options));

        /** @var array<string, mixed> $data */
        $data = $response['data'];

        return $this->mapList($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateList(string $id, array $data): XList
    {
        $response = $this->http->put("/2/lists/{$id}", $data);

        /** @var array<string, mixed> $responseData */
        $responseData = $response['data'] ?? array_merge(['id' => $id], $data);

        return $this->mapList($responseData);
    }

    public function deleteList(string $id): void
    {
        $this->http->delete("/2/lists/{$id}");
    }

    public function addListMember(string $listId, string $userId): void
    {
        $this->http->post("/2/lists/{$listId}/members", [
            'user_id' => $userId,
        ]);
    }

    public function removeListMember(string $listId, string $userId): void
    {
        $this->http->delete("/2/lists/{$listId}/members/{$userId}");
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function listMembers(
        string $listId,
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

        $response = $this->http->get("/2/lists/{$listId}/members", $query);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->listMembers(
            $listId, $maxResults, $token, $userFields,
        ));
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function listFollowers(
        string $listId,
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

        $response = $this->http->get("/2/lists/{$listId}/followers", $query);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->listFollowers(
            $listId, $maxResults, $token, $userFields,
        ));
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function listTweets(
        string $listId,
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

        $response = $this->http->get("/2/lists/{$listId}/tweets", $query);

        return $this->paginatedPosts($response, fn (string $token): PaginatedResult => $this->listTweets(
            $listId, $maxResults, $token, $tweetFields, $expansions, $userFields,
        ));
    }

    public function followList(string $userId, string $listId): void
    {
        $this->http->post("/2/users/{$userId}/followed_lists", [
            'list_id' => $listId,
        ]);
    }

    public function unfollowList(string $userId, string $listId): void
    {
        $this->http->delete("/2/users/{$userId}/followed_lists/{$listId}");
    }

    public function pinList(string $userId, string $listId): void
    {
        $this->http->post("/2/users/{$userId}/pinned_lists", [
            'list_id' => $listId,
        ]);
    }

    public function unpinList(string $userId, string $listId): void
    {
        $this->http->delete("/2/users/{$userId}/pinned_lists/{$listId}");
    }

    /**
     * @param  list<ListField>  $listFields
     * @return PaginatedResult<XList>
     */
    public function ownedLists(
        string $userId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $listFields = [],
    ): PaginatedResult {
        $query = array_merge(
            ['max_results' => $maxResults],
            $this->buildFieldQuery(listFields: $listFields),
        );

        if ($paginationToken !== null) {
            $query['pagination_token'] = $paginationToken;
        }

        $response = $this->http->get("/2/users/{$userId}/owned_lists", $query);

        return $this->paginatedLists($response, fn (string $token): PaginatedResult => $this->ownedLists(
            $userId, $maxResults, $token, $listFields,
        ));
    }
}
