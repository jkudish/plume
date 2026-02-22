<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\PaginatedResult;
use Plume\Data\User;
use Plume\Enums\Expansion;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait ManagesUsers
{
    /**
     * @param  list<UserField>  $userFields
     * @param  list<Expansion>  $expansions
     * @param  list<TweetField>  $tweetFields
     */
    public function getUser(
        string $id,
        array $userFields = [],
        array $expansions = [],
        array $tweetFields = [],
    ): User {
        $query = $this->buildFieldQuery($tweetFields, $expansions, $userFields);
        $response = $this->http->get("/2/users/{$id}", $query);

        /** @var array<string, mixed> $data */
        $data = $response['data'];

        return $this->mapUser($data);
    }

    /**
     * @param  list<string>  $ids
     * @param  list<UserField>  $userFields
     * @param  list<Expansion>  $expansions
     * @param  list<TweetField>  $tweetFields
     * @return list<User>
     */
    public function getUsers(
        array $ids,
        array $userFields = [],
        array $expansions = [],
        array $tweetFields = [],
    ): array {
        $query = array_merge(
            ['ids' => implode(',', $ids)],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields),
        );
        $response = $this->http->get('/2/users', $query);

        /** @var array<int, array<string, mixed>> $dataItems */
        $dataItems = $response['data'] ?? [];

        return array_values(array_map(fn (array $item): User => $this->mapUser($item), $dataItems));
    }

    /**
     * @param  list<UserField>  $userFields
     * @param  list<Expansion>  $expansions
     * @param  list<TweetField>  $tweetFields
     */
    public function getUserByUsername(
        string $username,
        array $userFields = [],
        array $expansions = [],
        array $tweetFields = [],
    ): User {
        $query = $this->buildFieldQuery($tweetFields, $expansions, $userFields);
        $response = $this->http->get("/2/users/by/username/{$username}", $query);

        /** @var array<string, mixed> $data */
        $data = $response['data'];

        return $this->mapUser($data);
    }

    /**
     * @param  list<string>  $usernames
     * @param  list<UserField>  $userFields
     * @param  list<Expansion>  $expansions
     * @param  list<TweetField>  $tweetFields
     * @return list<User>
     */
    public function getUsersByUsernames(
        array $usernames,
        array $userFields = [],
        array $expansions = [],
        array $tweetFields = [],
    ): array {
        $query = array_merge(
            ['usernames' => implode(',', $usernames)],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields),
        );
        $response = $this->http->get('/2/users/by', $query);

        /** @var array<int, array<string, mixed>> $dataItems */
        $dataItems = $response['data'] ?? [];

        return array_values(array_map(fn (array $item): User => $this->mapUser($item), $dataItems));
    }

    /**
     * @param  list<UserField>  $userFields
     * @param  list<Expansion>  $expansions
     * @param  list<TweetField>  $tweetFields
     */
    public function me(
        array $userFields = [],
        array $expansions = [],
        array $tweetFields = [],
    ): User {
        $query = $this->buildFieldQuery($tweetFields, $expansions, $userFields);
        $response = $this->http->get('/2/users/me', $query);

        /** @var array<string, mixed> $data */
        $data = $response['data'];

        return $this->mapUser($data);
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function searchUsers(
        string $query,
        int $maxResults = 10,
        ?string $nextToken = null,
        array $userFields = [],
    ): PaginatedResult {
        $params = array_merge(
            ['query' => $query, 'max_results' => $maxResults],
            $this->buildFieldQuery(userFields: $userFields),
        );

        if ($nextToken !== null) {
            $params['next_token'] = $nextToken;
        }

        $response = $this->http->get('/2/users/search', $params);

        return $this->paginatedUsers($response, fn (string $token): PaginatedResult => $this->searchUsers(
            $query, $maxResults, $token, $userFields,
        ));
    }
}
