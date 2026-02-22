<?php

declare(strict_types=1);

namespace Plume\Facades;

use Illuminate\Support\Facades\Facade;
use Plume\Contracts\XApiProvider;
use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Data\User;
use Plume\Data\XList;
use Plume\ScopedXClient;
use Plume\Testing\FakeXApiProvider;

/**
 * @method static Post createPost(string $text, array $options = [])
 * @method static void deletePost(string $id)
 * @method static Post getPost(string $id, array $tweetFields = [], array $expansions = [], array $userFields = [], array $mediaFields = [], array $pollFields = [], array $placeFields = [])
 * @method static list<Post> getPosts(array $ids, array $tweetFields = [], array $expansions = [], array $userFields = [], array $mediaFields = [], array $pollFields = [], array $placeFields = [])
 * @method static void hideReply(string $id)
 * @method static void unhideReply(string $id)
 * @method static PaginatedResult userTimeline(string $userId, int $maxResults = 10, ?string $paginationToken = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $exclude = [], array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static PaginatedResult mentionsTimeline(string $userId, int $maxResults = 10, ?string $paginationToken = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static PaginatedResult homeTimeline(string $userId, int $maxResults = 10, ?string $paginationToken = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $exclude = [], array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static PaginatedResult searchRecent(string $query, int $maxResults = 10, ?string $nextToken = null, ?\Plume\Enums\SortOrder $sortOrder = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static PaginatedResult searchAll(string $query, int $maxResults = 10, ?string $nextToken = null, ?\Plume\Enums\SortOrder $sortOrder = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static array countRecent(string $query, ?\Plume\Enums\Granularity $granularity = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null)
 * @method static array countAll(string $query, ?\Plume\Enums\Granularity $granularity = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null)
 * @method static User getUser(string $id, array $userFields = [], array $expansions = [], array $tweetFields = [])
 * @method static list<User> getUsers(array $ids, array $userFields = [], array $expansions = [], array $tweetFields = [])
 * @method static User getUserByUsername(string $username, array $userFields = [], array $expansions = [], array $tweetFields = [])
 * @method static list<User> getUsersByUsernames(array $usernames, array $userFields = [], array $expansions = [], array $tweetFields = [])
 * @method static User me(array $userFields = [], array $expansions = [], array $tweetFields = [])
 * @method static PaginatedResult searchUsers(string $query, int $maxResults = 10, ?string $nextToken = null, array $userFields = [])
 * @method static void follow(string $userId, string $targetUserId)
 * @method static void unfollow(string $userId, string $targetUserId)
 * @method static PaginatedResult followers(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = [])
 * @method static PaginatedResult following(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = [])
 * @method static void like(string $userId, string $tweetId)
 * @method static void unlike(string $userId, string $tweetId)
 * @method static PaginatedResult likingUsers(string $tweetId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = [])
 * @method static PaginatedResult likedTweets(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static void retweet(string $userId, string $tweetId)
 * @method static void undoRetweet(string $userId, string $tweetId)
 * @method static PaginatedResult retweetedBy(string $tweetId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = [])
 * @method static PaginatedResult quoteTweets(string $tweetId, int $maxResults = 100, ?string $paginationToken = null, array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static void bookmark(string $userId, string $tweetId)
 * @method static void removeBookmark(string $userId, string $tweetId)
 * @method static PaginatedResult bookmarks(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static void block(string $userId, string $targetUserId)
 * @method static void unblock(string $userId, string $targetUserId)
 * @method static PaginatedResult blockedUsers(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = [])
 * @method static void mute(string $userId, string $targetUserId)
 * @method static void unmute(string $userId, string $targetUserId)
 * @method static PaginatedResult mutedUsers(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = [])
 * @method static XList getList(string $id, array $listFields = [])
 * @method static XList createList(string $name, array $options = [])
 * @method static XList updateList(string $id, array $data)
 * @method static void deleteList(string $id)
 * @method static void addListMember(string $listId, string $userId)
 * @method static void removeListMember(string $listId, string $userId)
 * @method static PaginatedResult listMembers(string $listId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = [])
 * @method static PaginatedResult listFollowers(string $listId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = [])
 * @method static PaginatedResult listTweets(string $listId, int $maxResults = 100, ?string $paginationToken = null, array $tweetFields = [], array $expansions = [], array $userFields = [])
 * @method static void followList(string $userId, string $listId)
 * @method static void unfollowList(string $userId, string $listId)
 * @method static void pinList(string $userId, string $listId)
 * @method static void unpinList(string $userId, string $listId)
 * @method static PaginatedResult ownedLists(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $listFields = [])
 * @method static array uploadMedia(string $filePath, string $mediaType, ?string $mediaCategory = null)
 * @method static array initChunkedUpload(int $totalBytes, string $mediaType, ?string $mediaCategory = null)
 * @method static void appendChunk(string $mediaId, int $segmentIndex, string $chunkData)
 * @method static array finalizeUpload(string $mediaId)
 * @method static array uploadStatus(string $mediaId)
 * @method static void setMediaMetadata(string $mediaId, ?string $altText = null)
 * @method static ScopedXClient forUser(\Plume\Contracts\HasXCredentials|array $credentials)
 *
 * @see \Plume\Contracts\XApiProvider
 */
class X extends Facade
{
    public static function fake(): FakeXApiProvider
    {
        $fake = new FakeXApiProvider;
        static::swap($fake);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return XApiProvider::class;
    }
}
