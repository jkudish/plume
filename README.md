# Plume

X (Twitter) API v2 client for Laravel -- facades, typed DTOs, test fakes, and user-scoped operations.

![Tests](https://github.com/jkudish/plume/actions/workflows/ci.yml/badge.svg)
![Packagist Version](https://img.shields.io/packagist/v/jkudish/plume)
![Packagist Downloads](https://img.shields.io/packagist/dt/jkudish/plume)
![PHP Version](https://img.shields.io/packagist/php-v/jkudish/plume)
![Laravel Version](https://img.shields.io/badge/laravel-11.x%20%7C%2012.x-red)
![License](https://img.shields.io/packagist/l/jkudish/plume)

## Features

- Full X API v2 coverage: posts, timelines, search, users, likes, retweets, bookmarks, follows, blocks, mutes, lists, media upload
- Facade-based API via `X::` with full IDE autocomplete
- Typed DTOs (`Post`, `User`, `XList`, `Media`, `Place`, `Poll`, `PaginatedResult`, `Includes`)
- Active Record-style methods on DTOs (`$post->like()`, `$post->reply()`, `$user->follow()`)
- User-scoped client (`X::forUser($credentials)`) with automatic user ID resolution
- OAuth 2.0 with automatic token refresh and configurable callback
- Built-in pagination with `->nextPage()` cursor support
- 15 AI tools for Laravel AI SDK integration
- `X::fake()` test double with semantic assertions (`assertPostCreated`, `assertLiked`, `assertFollowed`, etc.)
- PHPStan level 8

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require jkudish/plume
```

Publish the config:

```bash
php artisan vendor:publish --tag=x-config
```

## Configuration

Add to your `.env`:

```env
# App-only bearer token (read-only public data)
X_BEARER_TOKEN=your-bearer-token

# OAuth 2.0 (required for user-context operations)
X_CLIENT_ID=your-client-id
X_CLIENT_SECRET=your-client-secret
X_REDIRECT_URI=https://your-app.test/x/callback

# Optional
X_API_TIMEOUT=30
```

### Token Refresh Callback

Persist refreshed tokens by binding a callback in your `AppServiceProvider`:

```php
$this->app->instance('x.token_refreshed', function (array $credentials) {
    // $credentials contains: access_token, refresh_token, expires_at
    $user = auth()->user();
    $user->update([
        'x_access_token' => $credentials['access_token'],
        'x_refresh_token' => $credentials['refresh_token'],
        'x_token_expires_at' => $credentials['expires_at'],
    ]);
});
```

## Quick Start

```php
use Plume\Facades\X;

// Post a tweet
$post = X::createPost('Hello from Plume!');

// Search recent tweets
$results = X::searchRecent('laravel');
foreach ($results->data as $post) {
    echo "{$post->text}\n";
}

// Get authenticated user
$me = X::me();
echo $me->username;
```

## Usage

### Posts

```php
use Plume\Facades\X;
use Plume\Enums\TweetField;
use Plume\Enums\Expansion;

// Create
$post = X::createPost('Hello world!');

// Create with options (reply, quote, media, poll)
$reply = X::createPost('Great thread!', [
    'reply' => ['in_reply_to_tweet_id' => '123456'],
]);

$quote = X::createPost('Check this out', [
    'quote_tweet_id' => '789012',
]);

$withMedia = X::createPost('Photo dump', [
    'media' => ['media_ids' => ['media-id-1', 'media-id-2']],
]);

// Fetch with field expansions
$post = X::getPost('123456',
    tweetFields: [TweetField::CreatedAt, TweetField::PublicMetrics],
    expansions: [Expansion::AuthorId],
);
echo $post->publicMetrics->likeCount;

// Batch fetch
$posts = X::getPosts(['123', '456', '789']);

// Delete
X::deletePost('123456');

// Hide/unhide replies
X::hideReply('123456');
X::unhideReply('123456');

// Active Record-style methods on Post DTO
$post->like('user-id');
$post->retweet('user-id');
$post->bookmark('user-id');
$post->reply('Nice post!');
$post->quote('Interesting take');
$post->hideReply();
$post->delete();
```

### Users & Profiles

```php
use Plume\Enums\UserField;

// Lookup by ID or username
$user = X::getUser('12345', userFields: [UserField::Description, UserField::PublicMetrics]);
$user = X::getUserByUsername('elonmusk');

// Batch lookup
$users = X::getUsers(['123', '456']);
$users = X::getUsersByUsernames(['laravel', 'taylorotwell']);

// Authenticated user
$me = X::me(userFields: [UserField::PublicMetrics]);
echo $me->publicMetrics->followersCount;

// Search users
$results = X::searchUsers('laravel', maxResults: 20);
```

### Timelines

```php
use Plume\Enums\Exclude;

// User's tweets
$timeline = X::userTimeline('user-id', maxResults: 20);

// Mentions
$mentions = X::mentionsTimeline('user-id');

// Home timeline (requires user context)
$home = X::homeTimeline('user-id', exclude: [Exclude::Retweets]);

// Pagination
$page = X::userTimeline('user-id', maxResults: 10);
while ($page !== null) {
    foreach ($page->data as $post) {
        echo $post->text . "\n";
    }
    $page = $page->nextPage();
}
```

### Search

```php
use Plume\Enums\SortOrder;
use Plume\Enums\Granularity;

// Recent search (last 7 days)
$results = X::searchRecent('laravel php', maxResults: 25, sortOrder: SortOrder::Recency);

// Full-archive search (Academic access)
$results = X::searchAll('from:taylorotwell laravel');

// Tweet counts
$counts = X::countRecent('laravel', granularity: Granularity::Day);
$counts = X::countAll('laravel', granularity: Granularity::Hour);
```

### Interactions

```php
// Likes
X::like('user-id', 'tweet-id');
X::unlike('user-id', 'tweet-id');
$likers = X::likingUsers('tweet-id');
$liked = X::likedTweets('user-id');

// Retweets
X::retweet('user-id', 'tweet-id');
X::undoRetweet('user-id', 'tweet-id');
$retweeters = X::retweetedBy('tweet-id');
$quotes = X::quoteTweets('tweet-id');

// Bookmarks
X::bookmark('user-id', 'tweet-id');
X::removeBookmark('user-id', 'tweet-id');
$bookmarks = X::bookmarks('user-id');

// Follows
X::follow('user-id', 'target-user-id');
X::unfollow('user-id', 'target-user-id');
$followers = X::followers('user-id');
$following = X::following('user-id');

// Blocks
X::block('user-id', 'target-user-id');
X::unblock('user-id', 'target-user-id');
$blocked = X::blockedUsers('user-id');

// Mutes
X::mute('user-id', 'target-user-id');
X::unmute('user-id', 'target-user-id');
$muted = X::mutedUsers('user-id');
```

### Lists

```php
use Plume\Enums\ListField;

// CRUD
$list = X::createList('My List', ['description' => 'A curated list']);
$list = X::getList('list-id', listFields: [ListField::Description]);
$list = X::updateList('list-id', ['name' => 'Updated Name']);
X::deleteList('list-id');

// Members
X::addListMember('list-id', 'user-id');
X::removeListMember('list-id', 'user-id');
$members = X::listMembers('list-id');

// List tweets and followers
$tweets = X::listTweets('list-id', maxResults: 50);
$followers = X::listFollowers('list-id');

// Follow/pin lists
X::followList('user-id', 'list-id');
X::unfollowList('user-id', 'list-id');
X::pinList('user-id', 'list-id');
X::unpinList('user-id', 'list-id');

// Owned lists
$lists = X::ownedLists('user-id');

// Active Record-style methods on XList DTO
$list->update(['name' => 'New Name']);
$list->addMember('user-id');
$list->removeMember('user-id');
$list->delete();
```

### Media Upload

```php
// Simple upload
$media = X::uploadMedia('/path/to/image.jpg', 'image/jpeg');
$post = X::createPost('Check this out!', [
    'media' => ['media_ids' => [$media['media_id']]],
]);

// Chunked upload (for large files/video)
$init = X::initChunkedUpload($totalBytes, 'video/mp4', 'tweet_video');
X::appendChunk($init['media_id'], 0, $chunkData);
$result = X::finalizeUpload($init['media_id']);
$status = X::uploadStatus($init['media_id']);

// Set alt text
X::setMediaMetadata($media['media_id'], altText: 'A beautiful sunset');
```

### Scoped Client

`ScopedXClient` operates on behalf of a specific user, automatically resolving the user's ID for endpoints that require it. No more passing `$userId` to every call.

```php
use Plume\Facades\X;

// From an array of credentials
$client = X::forUser([
    'access_token' => $token,
    'refresh_token' => $refreshToken,
    'expires_at' => $expiresAt,
]);

// Or from a model implementing HasXCredentials
$client = X::forUser($user);

// Inject user ID directly to avoid an extra /me API call
$client = X::forUser($credentials)->withUser('12345');
// Or pass a User DTO
$client = X::forUser($credentials)->withUser($user);

// All user-scoped operations resolve the user ID automatically
$client->like('tweet-id');        // no userId needed
$client->retweet('tweet-id');
$client->bookmark('tweet-id');
$client->follow('target-user-id');
$client->block('target-user-id');

$timeline = $client->userTimeline(maxResults: 20);
$bookmarks = $client->bookmarks();
$followers = $client->followers();
$myLists = $client->ownedLists();
```

Implement `HasXCredentials` on your User model:

```php
use Plume\Contracts\HasXCredentials;

class User extends Authenticatable implements HasXCredentials
{
    public function toXCredentials(): array
    {
        return [
            'access_token' => $this->x_access_token,
            'refresh_token' => $this->x_refresh_token,
            'expires_at' => $this->x_token_expires_at,
        ];
    }
}
```

### OAuth 2.0

Plume handles token refresh automatically. When a 401 response is received, the HTTP client attempts to refresh the token using the configured client credentials. If a `token_refreshed` callback is bound, it fires with the new credentials so you can persist them.

The refresh flow supports both public clients (PKCE) and confidential clients (client secret via Basic Auth), auto-detected based on whether `X_CLIENT_SECRET` is configured.

## AI Tools

Plume ships with 15 tools for the [Laravel AI SDK](https://github.com/laravel/ai), auto-tagged as `ai-tools` for discovery:

| Tool | ID | Description |
|------|----|-------------|
| `PlumeFetchTweetTool` | `plume:fetch-tweet` | Fetch a tweet by ID |
| `PlumePostTweetTool` | `plume:post-tweet` | Post a tweet (with optional reply) |
| `PlumeSearchTool` | `plume:search` | Search recent tweets |
| `PlumeHomeTimelineTool` | `plume:home-timeline` | Get home timeline |
| `PlumeMyTimelineTool` | `plume:my-timeline` | Get authenticated user's timeline |
| `PlumeMentionsTool` | `plume:mentions` | Get mentions timeline |
| `PlumeLikeTool` | `plume:like` | Like a tweet |
| `PlumeRetweetTool` | `plume:retweet` | Retweet a tweet |
| `PlumeBookmarkTool` | `plume:bookmark` | Bookmark a tweet |
| `PlumeBookmarksTool` | `plume:bookmarks` | List bookmarks |
| `PlumeFollowTool` | `plume:follow` | Follow a user |
| `PlumeFollowersTool` | `plume:followers` | List followers |
| `PlumeFollowingTool` | `plume:following` | List following |
| `PlumeProfileTool` | `plume:profile` | Get a user's profile |
| `PlumeUploadMediaTool` | `plume:upload-media` | Upload media |

All tools implement `Laravel\Ai\Contracts\Tool` and are automatically available to AI agents that resolve `ai-tools` tagged services.

## Testing

Use `X::fake()` to swap the real client with an in-memory fake that records all calls:

```php
use Plume\Facades\X;

it('creates a post', function () {
    $fake = X::fake();

    // Your code that calls X::createPost()...
    X::createPost('Hello from tests!');

    // Generic assertions
    $fake->assertCalled('createPost');
    $fake->assertCalledTimes('createPost', 1);
    $fake->assertNotCalled('deletePost');

    // Semantic assertions
    $fake->assertPostCreated('Hello');
    $fake->assertNothingCalled(); // fails -- createPost was called
});

it('asserts interactions', function () {
    $fake = X::fake();

    X::like('user-1', 'tweet-1');
    X::retweet('user-1', 'tweet-2');
    X::follow('user-1', 'target-1');
    X::bookmark('user-1', 'tweet-3');

    $fake->assertLiked('tweet-1');
    $fake->assertRetweeted('tweet-2');
    $fake->assertFollowed('target-1');
    $fake->assertBookmarked('tweet-3');
});

it('stubs return values', function () {
    $fake = X::fake();
    $fake->shouldReturn('searchRecent', new PaginatedResult(
        data: [new Post(id: '1', text: 'Stubbed result')],
        resultCount: 1,
    ));

    $results = X::searchRecent('test');
    expect($results->data)->toHaveCount(1);
    expect($results->data[0]->text)->toBe('Stubbed result');
});

it('simulates errors', function () {
    $fake = X::fake();
    $fake->shouldThrow('createPost', new RateLimitException('Rate limited', []));

    X::createPost('This will throw');
})->throws(RateLimitException::class);
```

### Available Semantic Assertions

| Method | Asserts that... |
|--------|----------------|
| `assertPostCreated(?string $textContains)` | A post was created, optionally containing text |
| `assertPostDeleted(string $id)` | A specific post was deleted |
| `assertLiked(string $tweetId)` | A tweet was liked |
| `assertRetweeted(string $tweetId)` | A tweet was retweeted |
| `assertBookmarked(string $tweetId)` | A tweet was bookmarked |
| `assertFollowed(string $targetUserId)` | A user was followed |
| `assertBlocked(string $targetUserId)` | A user was blocked |
| `assertMuted(string $targetUserId)` | A user was muted |
| `assertRepliedTo(string $tweetId)` | A reply was sent to a tweet |
| `assertSearched(?string $queryContains)` | A search was performed |
| `assertNothingPosted()` | No posts were created |
| `assertNothingCalled()` | No API calls were made |
| `assertForUserCalled()` | `forUser()` was called |

## API Reference

This package wraps the [X API v2](https://developer.x.com/en/docs/x-api). Refer to the official documentation for rate limits, field definitions, and access level requirements.

## Contributing

Contributions are welcome. Please open an issue or submit a pull request.

## Security

If you discover a security vulnerability, please email joey@jkudish.com instead of using the issue tracker.

## License

MIT. See [LICENSE](LICENSE) for details.
