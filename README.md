# Plume

X (Twitter) API v2 client for Laravel.

![Tests](https://github.com/jkudish/plume/actions/workflows/ci.yml/badge.svg)
![Packagist Version](https://img.shields.io/packagist/v/jkudish/plume)
![Packagist Downloads](https://img.shields.io/packagist/dt/jkudish/plume)
![PHP Version](https://img.shields.io/packagist/php-v/jkudish/plume)
![License](https://img.shields.io/packagist/l/jkudish/plume)

Plume wraps the entire X API v2 behind a clean Laravel facade. Typed DTOs, automatic pagination, user-scoped operations, OAuth token refresh, test fakes with semantic assertions, and 15 AI tools for the Laravel AI SDK.

## Install

```bash
composer require jkudish/plume
php artisan vendor:publish --tag=x-config
```

Add to `.env`:

```env
X_BEARER_TOKEN=your-bearer-token
X_CLIENT_ID=your-client-id
X_CLIENT_SECRET=your-client-secret
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

// Get your profile
$me = X::me();
echo $me->publicMetrics->followersCount;
```

## What's Covered

Every endpoint in the [X API v2](https://developer.x.com/en/docs/x-api):

| Domain | Methods |
|--------|---------|
| **Posts** | `createPost`, `deletePost`, `getPost`, `getPosts`, `hideReply`, `unhideReply` |
| **Timelines** | `userTimeline`, `mentionsTimeline`, `homeTimeline` |
| **Search** | `searchRecent`, `searchAll`, `countRecent`, `countAll` |
| **Users** | `getUser`, `getUsers`, `getUserByUsername`, `getUsersByUsernames`, `me`, `searchUsers` |
| **Likes** | `like`, `unlike`, `likingUsers`, `likedTweets` |
| **Retweets** | `retweet`, `undoRetweet`, `retweetedBy`, `quoteTweets` |
| **Bookmarks** | `bookmark`, `removeBookmark`, `bookmarks` |
| **Follows** | `follow`, `unfollow`, `followers`, `following` |
| **Blocks** | `block`, `unblock`, `blockedUsers` |
| **Mutes** | `mute`, `unmute`, `mutedUsers` |
| **Lists** | `createList`, `updateList`, `deleteList`, `getList`, `ownedLists`, `listTweets`, `listMembers`, `listFollowers`, `addListMember`, `removeListMember`, `followList`, `unfollowList`, `pinList`, `unpinList` |
| **Media** | `uploadMedia`, `initChunkedUpload`, `appendChunk`, `finalizeUpload`, `uploadStatus`, `setMediaMetadata` |

All methods are fully typed with enums for field selection (`TweetField`, `UserField`, `Expansion`, etc.) and return typed DTOs (`Post`, `User`, `XList`, `PaginatedResult`).

## Key Features

### Typed DTOs with Active Record Methods

```php
$post = X::getPost('123', tweetFields: [TweetField::PublicMetrics]);
echo $post->publicMetrics->likeCount;

// DTOs carry action methods
$post->like('user-id');
$post->reply('Nice post!');
$post->bookmark('user-id');
$post->delete();
```

### Automatic Pagination

```php
$page = X::userTimeline('user-id', maxResults: 100);
while ($page !== null) {
    foreach ($page->data as $post) {
        process($post);
    }
    $page = $page->nextPage();
}
```

### User-Scoped Client

`ScopedXClient` operates on behalf of a specific user. No more passing `$userId` to every call.

```php
// From credentials array or a model implementing HasXCredentials
$client = X::forUser($user);

// Inject user ID directly to skip the /me API call
$client = X::forUser($credentials)->withUser('12345');
// Or pass a User DTO
$client = X::forUser($credentials)->withUser($userDto);

// All calls auto-resolve the user ID
$client->like('tweet-id');
$client->bookmark('tweet-id');
$client->followers();
$client->userTimeline(maxResults: 20);
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

### OAuth 2.0 with Auto-Refresh

Plume handles token refresh automatically on 401 responses. Persist refreshed tokens with a callback:

```php
// In AppServiceProvider
config(['x.token_refreshed' => function (array $credentials) {
    auth()->user()->update([
        'x_access_token' => $credentials['access_token'],
        'x_refresh_token' => $credentials['refresh_token'],
        'x_token_expires_at' => $credentials['expires_at'],
    ]);
}]);
```

### Media Upload

```php
// Simple upload
$media = X::uploadMedia('/path/to/image.jpg', 'image/jpeg');
X::createPost('Check this out!', [
    'media' => ['media_ids' => [$media['media_id']]],
]);

// Chunked upload for large files
$init = X::initChunkedUpload($totalBytes, 'video/mp4', 'tweet_video');
X::appendChunk($init['media_id'], 0, $chunkData);
X::finalizeUpload($init['media_id']);
```

## Testing

`X::fake()` swaps the client with an in-memory fake that records all calls:

```php
use Plume\Facades\X;

it('creates a post', function () {
    $fake = X::fake();

    X::createPost('Hello from tests!');

    $fake->assertPostCreated('Hello');
    $fake->assertCalledTimes('createPost', 1);
});

it('tracks interactions', function () {
    $fake = X::fake();

    X::like('user-1', 'tweet-1');
    X::follow('user-1', 'target-1');

    $fake->assertLiked('tweet-1');
    $fake->assertFollowed('target-1');
});

it('stubs return values', function () {
    $fake = X::fake();
    $fake->shouldReturn('searchRecent', new PaginatedResult(
        data: [new Post(id: '1', text: 'Stubbed')],
        resultCount: 1,
    ));

    $results = X::searchRecent('test');
    expect($results->data[0]->text)->toBe('Stubbed');
});
```

**Semantic assertions:** `assertPostCreated`, `assertPostDeleted`, `assertLiked`, `assertRetweeted`, `assertBookmarked`, `assertFollowed`, `assertBlocked`, `assertMuted`, `assertRepliedTo`, `assertSearched`, `assertNothingPosted`, `assertNothingCalled`, `assertForUserCalled`.

## AI Tools

Plume ships 15 tools for the [Laravel AI SDK](https://github.com/laravel/ai) (requires PHP 8.4+). Install `laravel/ai` to use them:

```bash
composer require laravel/ai
```

Tools are tagged as `ai-tools` and implement `Laravel\Ai\Contracts\Tool`:

`plume:fetch-tweet`, `plume:post-tweet`, `plume:search`, `plume:home-timeline`, `plume:my-timeline`, `plume:mentions`, `plume:like`, `plume:retweet`, `plume:bookmark`, `plume:bookmarks`, `plume:follow`, `plume:followers`, `plume:following`, `plume:profile`, `plume:upload-media`

## Requirements

- PHP 8.2+ (AI tools require 8.4+)
- Laravel 11 or 12

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Run `composer test`, `composer phpstan`, and `composer lint` before submitting.

## Security

Email joey@jkudish.com to report vulnerabilities. See [SECURITY.md](SECURITY.md).

## Sponsoring

If Plume saves you time, consider [sponsoring development](https://github.com/sponsors/jkudish).

## License

MIT. See [LICENSE](LICENSE).
