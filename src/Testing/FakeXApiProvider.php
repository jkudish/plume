<?php

declare(strict_types=1);

namespace Plume\Testing;

use PHPUnit\Framework\Assert;
use Plume\Contracts\XApiProvider;
use Plume\Data\PaginatedResult;
use Plume\Data\Post;
use Plume\Data\User;
use Plume\Data\XList;
use Plume\Enums\Granularity;
use Plume\Enums\SortOrder;
use Plume\ScopedXClient;

class FakeXApiProvider implements XApiProvider
{
    /** @var list<array{method: string, args: list<mixed>}> */
    protected array $calls = [];

    /** @var array<string, mixed> */
    protected array $returnValues = [];

    /** @var array<string, \Throwable> */
    protected array $throwables = [];

    protected bool $forUserCalled = false;

    protected int $nextId = 1;

    // ── Configurable behavior ───────────────────────────────

    public function shouldReturn(string $method, mixed $value): static
    {
        $this->returnValues[$method] = $value;

        return $this;
    }

    public function shouldThrow(string $method, \Throwable $exception): static
    {
        $this->throwables[$method] = $exception;

        return $this;
    }

    // ── Generic assertions ──────────────────────────────────

    public function assertCalled(string $method, ?\Closure $callback = null): void
    {
        $calls = array_filter($this->calls, fn ($call) => $call['method'] === $method);
        Assert::assertNotEmpty($calls, "Expected [{$method}] to be called, but it was not.");

        if ($callback !== null) {
            $matched = array_filter($calls, fn ($call) => $callback($call['args']));
            Assert::assertNotEmpty($matched, "Expected [{$method}] to be called matching callback, but no matching call found.");
        }
    }

    public function assertNotCalled(string $method): void
    {
        $calls = array_filter($this->calls, fn ($call) => $call['method'] === $method);
        Assert::assertEmpty($calls, "Expected [{$method}] not to be called, but it was called ".count($calls).' time(s).');
    }

    public function assertCalledTimes(string $method, int $times): void
    {
        $count = count(array_filter($this->calls, fn ($call) => $call['method'] === $method));
        Assert::assertEquals($times, $count, "Expected [{$method}] to be called {$times} time(s), but was called {$count} time(s).");
    }

    // ── Semantic assertions ─────────────────────────────────

    public function assertPostCreated(?string $textContains = null): void
    {
        $this->assertCalled('createPost', function ($args) use ($textContains) {
            if ($textContains !== null && ! str_contains($args[0], $textContains)) {
                return false;
            }

            return true;
        });
    }

    public function assertPostDeleted(string $id): void
    {
        $this->assertCalled('deletePost', fn ($args) => $args[0] === $id);
    }

    public function assertLiked(string $tweetId): void
    {
        $this->assertCalled('like', fn ($args) => $args[1] === $tweetId);
    }

    public function assertRetweeted(string $tweetId): void
    {
        $this->assertCalled('retweet', fn ($args) => $args[1] === $tweetId);
    }

    public function assertBookmarked(string $tweetId): void
    {
        $this->assertCalled('bookmark', fn ($args) => $args[1] === $tweetId);
    }

    public function assertFollowed(string $targetUserId): void
    {
        $this->assertCalled('follow', fn ($args) => $args[1] === $targetUserId);
    }

    public function assertBlocked(string $targetUserId): void
    {
        $this->assertCalled('block', fn ($args) => $args[1] === $targetUserId);
    }

    public function assertMuted(string $targetUserId): void
    {
        $this->assertCalled('mute', fn ($args) => $args[1] === $targetUserId);
    }

    public function assertRepliedTo(string $tweetId): void
    {
        $this->assertCalled('createPost', function ($args) use ($tweetId) {
            $options = $args[1] ?? [];

            return isset($options['reply']['in_reply_to_tweet_id'])
                && $options['reply']['in_reply_to_tweet_id'] === $tweetId;
        });
    }

    public function assertSearched(?string $queryContains = null): void
    {
        $this->assertCalled('searchRecent', function ($args) use ($queryContains) {
            if ($queryContains !== null && ! str_contains($args[0], $queryContains)) {
                return false;
            }

            return true;
        });
    }

    public function assertNothingPosted(): void
    {
        $this->assertNotCalled('createPost');
    }

    public function assertNothingCalled(): void
    {
        Assert::assertEmpty($this->calls, 'Expected no methods to be called, but '.count($this->calls).' call(s) were made.');
    }

    public function assertForUserCalled(): void
    {
        Assert::assertTrue($this->forUserCalled, 'Expected forUser() to be called, but it was not.');
    }

    // ── Record helper ───────────────────────────────────────

    /**
     * @param  list<mixed>  $args
     */
    protected function record(string $method, array $args): mixed
    {
        $this->calls[] = ['method' => $method, 'args' => $args];

        if (isset($this->throwables[$method])) {
            throw $this->throwables[$method];
        }

        return $this->returnValues[$method] ?? null;
    }

    // ── Posts ────────────────────────────────────────────────

    public function createPost(string $text, array $options = []): Post
    {
        $result = $this->record('createPost', [$text, $options]);

        return $result ?? (new Post(
            id: (string) $this->nextId++,
            text: $text,
        ))->withProvider($this);
    }

    public function deletePost(string $id): void
    {
        $this->record('deletePost', [$id]);
    }

    public function getPost(string $id, array $tweetFields = [], array $expansions = [], array $userFields = [], array $mediaFields = [], array $pollFields = [], array $placeFields = []): Post
    {
        $result = $this->record('getPost', [$id, $tweetFields, $expansions, $userFields, $mediaFields, $pollFields, $placeFields]);

        return $result ?? (new Post(id: $id, text: 'Fake post'))->withProvider($this);
    }

    public function getPosts(array $ids, array $tweetFields = [], array $expansions = [], array $userFields = [], array $mediaFields = [], array $pollFields = [], array $placeFields = []): array
    {
        $result = $this->record('getPosts', [$ids, $tweetFields, $expansions, $userFields, $mediaFields, $pollFields, $placeFields]);

        return $result ?? array_map(fn (string $id): Post => (new Post(id: $id, text: 'Fake post'))->withProvider($this), $ids);
    }

    public function hideReply(string $id): void
    {
        $this->record('hideReply', [$id]);
    }

    public function unhideReply(string $id): void
    {
        $this->record('unhideReply', [$id]);
    }

    // ── Timelines ───────────────────────────────────────────

    public function userTimeline(string $userId, int $maxResults = 10, ?string $paginationToken = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $exclude = [], array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('userTimeline', [$userId, $maxResults, $paginationToken, $sinceId, $untilId, $startTime, $endTime, $exclude, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function mentionsTimeline(string $userId, int $maxResults = 10, ?string $paginationToken = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('mentionsTimeline', [$userId, $maxResults, $paginationToken, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function homeTimeline(string $userId, int $maxResults = 10, ?string $paginationToken = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $exclude = [], array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('homeTimeline', [$userId, $maxResults, $paginationToken, $sinceId, $untilId, $startTime, $endTime, $exclude, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Search ──────────────────────────────────────────────

    public function searchRecent(string $query, int $maxResults = 10, ?string $nextToken = null, ?SortOrder $sortOrder = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('searchRecent', [$query, $maxResults, $nextToken, $sortOrder, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function searchAll(string $query, int $maxResults = 10, ?string $nextToken = null, ?SortOrder $sortOrder = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null, array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('searchAll', [$query, $maxResults, $nextToken, $sortOrder, $sinceId, $untilId, $startTime, $endTime, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function countRecent(string $query, ?Granularity $granularity = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null): array
    {
        return $this->record('countRecent', [$query, $granularity]) ?? ['data' => [], 'meta' => ['total_tweet_count' => 0]];
    }

    public function countAll(string $query, ?Granularity $granularity = null, ?string $sinceId = null, ?string $untilId = null, ?string $startTime = null, ?string $endTime = null): array
    {
        return $this->record('countAll', [$query, $granularity]) ?? ['data' => [], 'meta' => ['total_tweet_count' => 0]];
    }

    // ── Users ───────────────────────────────────────────────

    public function getUser(string $id, array $userFields = [], array $expansions = [], array $tweetFields = []): User
    {
        $result = $this->record('getUser', [$id, $userFields, $expansions, $tweetFields]);

        return $result ?? (new User(id: $id, name: 'Fake User', username: 'fakeuser'))->withProvider($this);
    }

    public function getUsers(array $ids, array $userFields = [], array $expansions = [], array $tweetFields = []): array
    {
        $result = $this->record('getUsers', [$ids, $userFields, $expansions, $tweetFields]);

        return $result ?? array_map(fn (string $id): User => (new User(id: $id, name: 'Fake User', username: 'fakeuser'.$id))->withProvider($this), $ids);
    }

    public function getUserByUsername(string $username, array $userFields = [], array $expansions = [], array $tweetFields = []): User
    {
        $result = $this->record('getUserByUsername', [$username, $userFields, $expansions, $tweetFields]);

        return $result ?? (new User(id: '1', name: 'Fake User', username: $username))->withProvider($this);
    }

    public function getUsersByUsernames(array $usernames, array $userFields = [], array $expansions = [], array $tweetFields = []): array
    {
        $result = $this->record('getUsersByUsernames', [$usernames, $userFields, $expansions, $tweetFields]);

        return $result ?? array_values(array_map(fn (string $u): User => (new User(id: (string) $this->nextId++, name: 'Fake', username: $u))->withProvider($this), $usernames));
    }

    public function me(array $userFields = [], array $expansions = [], array $tweetFields = []): User
    {
        $result = $this->record('me', [$userFields, $expansions, $tweetFields]);

        return $result ?? (new User(id: '999', name: 'Test User', username: 'testuser'))->withProvider($this);
    }

    public function searchUsers(string $query, int $maxResults = 10, ?string $nextToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('searchUsers', [$query, $maxResults, $nextToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Follows ─────────────────────────────────────────────

    public function follow(string $userId, string $targetUserId): void
    {
        $this->record('follow', [$userId, $targetUserId]);
    }

    public function unfollow(string $userId, string $targetUserId): void
    {
        $this->record('unfollow', [$userId, $targetUserId]);
    }

    public function followers(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('followers', [$userId, $maxResults, $paginationToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function following(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('following', [$userId, $maxResults, $paginationToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Likes ───────────────────────────────────────────────

    public function like(string $userId, string $tweetId): void
    {
        $this->record('like', [$userId, $tweetId]);
    }

    public function unlike(string $userId, string $tweetId): void
    {
        $this->record('unlike', [$userId, $tweetId]);
    }

    public function likingUsers(string $tweetId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('likingUsers', [$tweetId, $maxResults, $paginationToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function likedTweets(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('likedTweets', [$userId, $maxResults, $paginationToken, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Retweets ────────────────────────────────────────────

    public function retweet(string $userId, string $tweetId): void
    {
        $this->record('retweet', [$userId, $tweetId]);
    }

    public function undoRetweet(string $userId, string $tweetId): void
    {
        $this->record('undoRetweet', [$userId, $tweetId]);
    }

    public function retweetedBy(string $tweetId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('retweetedBy', [$tweetId, $maxResults, $paginationToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function quoteTweets(string $tweetId, int $maxResults = 100, ?string $paginationToken = null, array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('quoteTweets', [$tweetId, $maxResults, $paginationToken, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Bookmarks ───────────────────────────────────────────

    public function bookmark(string $userId, string $tweetId): void
    {
        $this->record('bookmark', [$userId, $tweetId]);
    }

    public function removeBookmark(string $userId, string $tweetId): void
    {
        $this->record('removeBookmark', [$userId, $tweetId]);
    }

    public function bookmarks(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('bookmarks', [$userId, $maxResults, $paginationToken, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Blocks ──────────────────────────────────────────────

    public function block(string $userId, string $targetUserId): void
    {
        $this->record('block', [$userId, $targetUserId]);
    }

    public function unblock(string $userId, string $targetUserId): void
    {
        $this->record('unblock', [$userId, $targetUserId]);
    }

    public function blockedUsers(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('blockedUsers', [$userId, $maxResults, $paginationToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Mutes ───────────────────────────────────────────────

    public function mute(string $userId, string $targetUserId): void
    {
        $this->record('mute', [$userId, $targetUserId]);
    }

    public function unmute(string $userId, string $targetUserId): void
    {
        $this->record('unmute', [$userId, $targetUserId]);
    }

    public function mutedUsers(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('mutedUsers', [$userId, $maxResults, $paginationToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Lists ───────────────────────────────────────────────

    public function getList(string $id, array $listFields = []): XList
    {
        $result = $this->record('getList', [$id, $listFields]);

        return $result ?? (new XList(id: $id, name: 'Fake List'))->withProvider($this);
    }

    public function createList(string $name, array $options = []): XList
    {
        $result = $this->record('createList', [$name, $options]);

        return $result ?? (new XList(id: (string) $this->nextId++, name: $name))->withProvider($this);
    }

    public function updateList(string $id, array $data): XList
    {
        $result = $this->record('updateList', [$id, $data]);

        return $result ?? (new XList(id: $id, name: $data['name'] ?? 'Updated List'))->withProvider($this);
    }

    public function deleteList(string $id): void
    {
        $this->record('deleteList', [$id]);
    }

    public function addListMember(string $listId, string $userId): void
    {
        $this->record('addListMember', [$listId, $userId]);
    }

    public function removeListMember(string $listId, string $userId): void
    {
        $this->record('removeListMember', [$listId, $userId]);
    }

    public function listMembers(string $listId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('listMembers', [$listId, $maxResults, $paginationToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function listFollowers(string $listId, int $maxResults = 100, ?string $paginationToken = null, array $userFields = []): PaginatedResult
    {
        return $this->record('listFollowers', [$listId, $maxResults, $paginationToken, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function listTweets(string $listId, int $maxResults = 100, ?string $paginationToken = null, array $tweetFields = [], array $expansions = [], array $userFields = []): PaginatedResult
    {
        return $this->record('listTweets', [$listId, $maxResults, $paginationToken, $tweetFields, $expansions, $userFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    public function followList(string $userId, string $listId): void
    {
        $this->record('followList', [$userId, $listId]);
    }

    public function unfollowList(string $userId, string $listId): void
    {
        $this->record('unfollowList', [$userId, $listId]);
    }

    public function pinList(string $userId, string $listId): void
    {
        $this->record('pinList', [$userId, $listId]);
    }

    public function unpinList(string $userId, string $listId): void
    {
        $this->record('unpinList', [$userId, $listId]);
    }

    public function ownedLists(string $userId, int $maxResults = 100, ?string $paginationToken = null, array $listFields = []): PaginatedResult
    {
        return $this->record('ownedLists', [$userId, $maxResults, $paginationToken, $listFields]) ?? new PaginatedResult(data: [], resultCount: 0);
    }

    // ── Media ───────────────────────────────────────────────

    public function uploadMedia(string $filePath, string $mediaType, ?string $mediaCategory = null): array
    {
        return $this->record('uploadMedia', [$filePath, $mediaType, $mediaCategory]) ?? ['media_id' => (string) $this->nextId++];
    }

    public function initChunkedUpload(int $totalBytes, string $mediaType, ?string $mediaCategory = null): array
    {
        return $this->record('initChunkedUpload', [$totalBytes, $mediaType, $mediaCategory]) ?? ['media_id' => (string) $this->nextId++];
    }

    public function appendChunk(string $mediaId, int $segmentIndex, string $chunkData): void
    {
        $this->record('appendChunk', [$mediaId, $segmentIndex, $chunkData]);
    }

    public function finalizeUpload(string $mediaId): array
    {
        return $this->record('finalizeUpload', [$mediaId]) ?? ['media_id' => $mediaId];
    }

    public function uploadStatus(string $mediaId): array
    {
        return $this->record('uploadStatus', [$mediaId]) ?? ['media_id' => $mediaId, 'processing_info' => ['state' => 'succeeded']];
    }

    public function setMediaMetadata(string $mediaId, ?string $altText = null): void
    {
        $this->record('setMediaMetadata', [$mediaId, $altText]);
    }

    // ── Scoped ──────────────────────────────────────────────

    public function forUser(\Plume\Contracts\HasXCredentials|array $credentials): ScopedXClient
    {
        $this->forUserCalled = true;

        return new ScopedXClient($this);
    }
}
