<?php

declare(strict_types=1);

namespace Plume;

use Plume\Contracts\XApiProvider;
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

class ScopedXClient
{
    private ?string $userId = null;

    public function __construct(
        protected XApiProvider $client,
    ) {}

    // ── Posts ────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $options
     */
    public function createPost(string $text, array $options = []): Post
    {
        return $this->client->createPost($text, $options);
    }

    public function deletePost(string $id): void
    {
        $this->client->deletePost($id);
    }

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
    ): Post {
        return $this->client->getPost($id, $tweetFields, $expansions, $userFields, $mediaFields, $pollFields, $placeFields);
    }

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
    ): array {
        return $this->client->getPosts($ids, $tweetFields, $expansions, $userFields, $mediaFields, $pollFields, $placeFields);
    }

    public function hideReply(string $id): void
    {
        $this->client->hideReply($id);
    }

    public function unhideReply(string $id): void
    {
        $this->client->unhideReply($id);
    }

    // ── Timelines (userId resolved automatically) ───────────

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<Exclude>  $exclude
     * @return PaginatedResult<Post>
     */
    public function userTimeline(
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
        return $this->client->userTimeline(
            $this->resolveUserId(), $maxResults, $paginationToken, $sinceId, $untilId, $startTime, $endTime, $exclude, $tweetFields, $expansions, $userFields,
        );
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function mentionsTimeline(
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
        return $this->client->mentionsTimeline(
            $this->resolveUserId(), $maxResults, $paginationToken, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields,
        );
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<Exclude>  $exclude
     * @return PaginatedResult<Post>
     */
    public function homeTimeline(
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
        return $this->client->homeTimeline(
            $this->resolveUserId(), $maxResults, $paginationToken, $sinceId, $untilId, $startTime, $endTime, $exclude, $tweetFields, $expansions, $userFields,
        );
    }

    // ── Search (pass-through) ───────────────────────────────

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
    ): PaginatedResult {
        return $this->client->searchRecent($query, $maxResults, $nextToken, $sortOrder, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields);
    }

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
    ): PaginatedResult {
        return $this->client->searchAll($query, $maxResults, $nextToken, $sortOrder, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields);
    }

    // ── Users (pass-through) ────────────────────────────────

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
        return $this->client->getUser($id, $userFields, $expansions, $tweetFields);
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
        return $this->client->getUsers($ids, $userFields, $expansions, $tweetFields);
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
        return $this->client->getUserByUsername($username, $userFields, $expansions, $tweetFields);
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
        return $this->client->getUsersByUsernames($usernames, $userFields, $expansions, $tweetFields);
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
        return $this->client->me($userFields, $expansions, $tweetFields);
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
        return $this->client->searchUsers($query, $maxResults, $nextToken, $userFields);
    }

    // ── Likes (userId resolved automatically) ───────────────

    public function like(string $tweetId): void
    {
        $this->client->like($this->resolveUserId(), $tweetId);
    }

    public function unlike(string $tweetId): void
    {
        $this->client->unlike($this->resolveUserId(), $tweetId);
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
        return $this->client->likingUsers($tweetId, $maxResults, $paginationToken, $userFields);
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<Post>
     */
    public function likedTweets(
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
    ): PaginatedResult {
        return $this->client->likedTweets($this->resolveUserId(), $maxResults, $paginationToken, $tweetFields, $expansions, $userFields);
    }

    // ── Retweets (userId resolved automatically) ────────────

    public function retweet(string $tweetId): void
    {
        $this->client->retweet($this->resolveUserId(), $tweetId);
    }

    public function undoRetweet(string $tweetId): void
    {
        $this->client->undoRetweet($this->resolveUserId(), $tweetId);
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
        return $this->client->retweetedBy($tweetId, $maxResults, $paginationToken, $userFields);
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
        return $this->client->quoteTweets($tweetId, $maxResults, $paginationToken, $tweetFields, $expansions, $userFields);
    }

    // ── Bookmarks (userId resolved automatically) ───────────

    public function bookmark(string $tweetId): void
    {
        $this->client->bookmark($this->resolveUserId(), $tweetId);
    }

    public function removeBookmark(string $tweetId): void
    {
        $this->client->removeBookmark($this->resolveUserId(), $tweetId);
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<MediaField>  $mediaFields
     * @return PaginatedResult<Post>
     */
    public function bookmarks(
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
        array $mediaFields = [],
    ): PaginatedResult {
        return $this->client->bookmarks($this->resolveUserId(), $maxResults, $paginationToken, $tweetFields, $expansions, $userFields, $mediaFields);
    }

    // ── Follows (userId resolved automatically) ─────────────

    public function follow(string $targetUserId): void
    {
        $this->client->follow($this->resolveUserId(), $targetUserId);
    }

    public function unfollow(string $targetUserId): void
    {
        $this->client->unfollow($this->resolveUserId(), $targetUserId);
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function followers(
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult {
        return $this->client->followers($this->resolveUserId(), $maxResults, $paginationToken, $userFields);
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function following(
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult {
        return $this->client->following($this->resolveUserId(), $maxResults, $paginationToken, $userFields);
    }

    // ── Blocks (userId resolved automatically) ──────────────

    public function block(string $targetUserId): void
    {
        $this->client->block($this->resolveUserId(), $targetUserId);
    }

    public function unblock(string $targetUserId): void
    {
        $this->client->unblock($this->resolveUserId(), $targetUserId);
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function blockedUsers(
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult {
        return $this->client->blockedUsers($this->resolveUserId(), $maxResults, $paginationToken, $userFields);
    }

    // ── Mutes (userId resolved automatically) ───────────────

    public function mute(string $targetUserId): void
    {
        $this->client->mute($this->resolveUserId(), $targetUserId);
    }

    public function unmute(string $targetUserId): void
    {
        $this->client->unmute($this->resolveUserId(), $targetUserId);
    }

    /**
     * @param  list<UserField>  $userFields
     * @return PaginatedResult<User>
     */
    public function mutedUsers(
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $userFields = [],
    ): PaginatedResult {
        return $this->client->mutedUsers($this->resolveUserId(), $maxResults, $paginationToken, $userFields);
    }

    // ── Lists (userId resolved automatically where needed) ──

    /**
     * @param  list<ListField>  $listFields
     */
    public function getList(string $id, array $listFields = []): XList
    {
        return $this->client->getList($id, $listFields);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function createList(string $name, array $options = []): XList
    {
        return $this->client->createList($name, $options);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateList(string $id, array $data): XList
    {
        return $this->client->updateList($id, $data);
    }

    public function deleteList(string $id): void
    {
        $this->client->deleteList($id);
    }

    public function addListMember(string $listId, string $userId): void
    {
        $this->client->addListMember($listId, $userId);
    }

    public function removeListMember(string $listId, string $userId): void
    {
        $this->client->removeListMember($listId, $userId);
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
        return $this->client->listMembers($listId, $maxResults, $paginationToken, $userFields);
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
        return $this->client->listFollowers($listId, $maxResults, $paginationToken, $userFields);
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
        return $this->client->listTweets($listId, $maxResults, $paginationToken, $tweetFields, $expansions, $userFields);
    }

    public function followList(string $listId): void
    {
        $this->client->followList($this->resolveUserId(), $listId);
    }

    public function unfollowList(string $listId): void
    {
        $this->client->unfollowList($this->resolveUserId(), $listId);
    }

    public function pinList(string $listId): void
    {
        $this->client->pinList($this->resolveUserId(), $listId);
    }

    public function unpinList(string $listId): void
    {
        $this->client->unpinList($this->resolveUserId(), $listId);
    }

    /**
     * @param  list<ListField>  $listFields
     * @return PaginatedResult<XList>
     */
    public function ownedLists(
        int $maxResults = 100,
        ?string $paginationToken = null,
        array $listFields = [],
    ): PaginatedResult {
        return $this->client->ownedLists($this->resolveUserId(), $maxResults, $paginationToken, $listFields);
    }

    // ── Media (pass-through) ────────────────────────────────

    /**
     * @return array{media_id: string}
     */
    public function uploadMedia(string $filePath, string $mediaType, ?string $mediaCategory = null): array
    {
        return $this->client->uploadMedia($filePath, $mediaType, $mediaCategory);
    }

    /**
     * @return array{media_id: string}
     */
    public function initChunkedUpload(int $totalBytes, string $mediaType, ?string $mediaCategory = null): array
    {
        return $this->client->initChunkedUpload($totalBytes, $mediaType, $mediaCategory);
    }

    public function appendChunk(string $mediaId, int $segmentIndex, string $chunkData): void
    {
        $this->client->appendChunk($mediaId, $segmentIndex, $chunkData);
    }

    /**
     * @return array<string, mixed>
     */
    public function finalizeUpload(string $mediaId): array
    {
        return $this->client->finalizeUpload($mediaId);
    }

    public function setMediaMetadata(string $mediaId, ?string $altText = null): void
    {
        $this->client->setMediaMetadata($mediaId, $altText);
    }

    /**
     * @return array<string, mixed>
     */
    public function uploadStatus(string $mediaId): array
    {
        return $this->client->uploadStatus($mediaId);
    }

    // ── Counts (pass-through) ───────────────────────────────

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
    ): array {
        return $this->client->countRecent($query, $granularity, $sinceId, $untilId, $startTime, $endTime);
    }

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
    ): array {
        return $this->client->countAll($query, $granularity, $sinceId, $untilId, $startTime, $endTime);
    }

    // ── Internal ────────────────────────────────────────────

    protected function resolveUserId(): string
    {
        if ($this->userId === null) {
            $me = $this->client->me();
            $this->userId = $me->id;
        }

        return $this->userId;
    }
}
