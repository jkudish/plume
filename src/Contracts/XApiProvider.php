<?php

declare(strict_types=1);

namespace Plume\Contracts;

use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Data\User;
use Plume\Data\XList;
use Plume\Enums\Exclude;
use Plume\Enums\Expansion;
use Plume\Enums\Granularity;
use Plume\Enums\ListField;
use Plume\Enums\MediaField;
use Plume\Enums\PlaceField;
use Plume\Enums\PollField;
use Plume\Enums\SortOrder;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;
use Plume\ScopedXClient;

interface XApiProvider
{
    // ── Posts ────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $options
     */
    public function createPost(string $text, array $options = []): Post;

    public function deletePost(string $id): void;

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<MediaField>  $mediaFields
     * @param  list<PollField>  $pollFields
     * @param  list<PlaceField>  $placeFields
     */
    public function getPost(
        string $id,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
        array $mediaFields = [],
        array $pollFields = [],
        array $placeFields = [],
    ): Post;

    /**
     * @param  list<string>  $ids
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<MediaField>  $mediaFields
     * @param  list<PollField>  $pollFields
     * @param  list<PlaceField>  $placeFields
     * @return list<Post>
     */
    public function getPosts(
        array $ids,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
        array $mediaFields = [],
        array $pollFields = [],
        array $placeFields = [],
    ): array;

    public function hideReply(string $id): void;

    public function unhideReply(string $id): void;

    // ── Timelines ───────────────────────────────────────────

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
    ): PaginatedResult;

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
    ): PaginatedResult;

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
    ): PaginatedResult;

    // ── Search ──────────────────────────────────────────────

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
    ): PaginatedResult;

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
    ): PaginatedResult;

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
    ): array;

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
    ): array;

    // ── Users ───────────────────────────────────────────────

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
    ): User;

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
    ): array;

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
    ): User;

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
    ): array;

    /**
     * @param  list<UserField>  $userFields
     * @param  list<Expansion>  $expansions
     * @param  list<TweetField>  $tweetFields
     */
    public function me(
        array $userFields = [],
        array $expansions = [],
        array $tweetFields = [],
    ): User;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function searchUsers(
        string $query,
        int $maxResults = 10,
        ?string $nextToken = null,
        array $userFields = [],
    ): PaginatedResult;

    // ── Follows ─────────────────────────────────────────────

    public function follow(string $userId, string $targetUserId): void;

    public function unfollow(string $userId, string $targetUserId): void;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function followers(
        string $userId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function following(
        string $userId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult;

    // ── Likes ───────────────────────────────────────────────

    public function like(string $userId, string $tweetId): void;

    public function unlike(string $userId, string $tweetId): void;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function likingUsers(
        string $tweetId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult;

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
    ): PaginatedResult;

    // ── Retweets ────────────────────────────────────────────

    public function retweet(string $userId, string $tweetId): void;

    public function undoRetweet(string $userId, string $tweetId): void;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function retweetedBy(
        string $tweetId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult;

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
    ): PaginatedResult;

    // ── Bookmarks ───────────────────────────────────────────

    public function bookmark(string $userId, string $tweetId): void;

    public function removeBookmark(string $userId, string $tweetId): void;

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<MediaField>  $mediaFields
     * @return PaginatedResult<Post>
     */
    public function bookmarks(
        string $userId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
        array $mediaFields = [],
    ): PaginatedResult;

    // ── Blocks ──────────────────────────────────────────────

    public function block(string $userId, string $targetUserId): void;

    public function unblock(string $userId, string $targetUserId): void;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function blockedUsers(
        string $userId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult;

    // ── Mutes ───────────────────────────────────────────────

    public function mute(string $userId, string $targetUserId): void;

    public function unmute(string $userId, string $targetUserId): void;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function mutedUsers(
        string $userId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult;

    // ── Lists ───────────────────────────────────────────────

    /**
     * @param  list<ListField>  $listFields
     */
    public function getList(
        string $id,
        array $listFields = [],
    ): XList;

    /**
     * @param  array<string, mixed>  $options
     */
    public function createList(string $name, array $options = []): XList;

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateList(string $id, array $data): XList;

    public function deleteList(string $id): void;

    public function addListMember(string $listId, string $userId): void;

    public function removeListMember(string $listId, string $userId): void;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function listMembers(
        string $listId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult;

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function listFollowers(
        string $listId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult;

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
    ): PaginatedResult;

    public function followList(string $userId, string $listId): void;

    public function unfollowList(string $userId, string $listId): void;

    public function pinList(string $userId, string $listId): void;

    public function unpinList(string $userId, string $listId): void;

    /**
     * @param  list<ListField>  $listFields
     * @return PaginatedResult<XList>
     */
    public function ownedLists(
        string $userId,
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $listFields = [],
    ): PaginatedResult;

    // ── Media ───────────────────────────────────────────────

    /**
     * @return array{media_id: string}
     */
    public function uploadMedia(string $filePath, string $mediaType, ?string $mediaCategory = null): array;

    /**
     * @return array{media_id: string}
     */
    public function initChunkedUpload(int $totalBytes, string $mediaType, ?string $mediaCategory = null): array;

    public function appendChunk(string $mediaId, int $segmentIndex, string $chunkData): void;

    /**
     * @return array<string, mixed>
     */
    public function finalizeUpload(string $mediaId): array;

    /**
     * @return array<string, mixed>
     */
    public function uploadStatus(string $mediaId): array;

    public function setMediaMetadata(string $mediaId, ?string $altText = null): void;

    // ── Scoped ──────────────────────────────────────────────

    /**
     * @param  HasXCredentials|array<string, string|null>  $credentials
     */
    public function forUser(HasXCredentials|array $credentials): ScopedXClient;
}
