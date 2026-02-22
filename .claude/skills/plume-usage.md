---
name: plume-cli
description: "Plume CLI — 41 artisan commands for the X (Twitter) API v2"
triggers:
  - "plume"
  - "plume:"
  - "tweet"
  - "x api cli"
---

# Plume CLI Commands

41 artisan commands for the X API v2. Requires `X_BEARER_TOKEN` in `.env`.

## Common Patterns

- `--format=json` on read commands for machine-readable output
- `--force` on destructive commands to skip confirmation
- `--max-results=N` on list/timeline commands to control page size
- User ID is auto-resolved via `/me` — no need to pass it

## Commands

### Profile

```bash
plume:me {--format=table}
```

### Posts

```bash
plume:post {--text= : Post text} {--reply-to= : Tweet ID to reply to} {--quote= : Tweet ID to quote} {--format=table}
plume:get-post {id} {--format=table}
plume:delete-post {id} {--force}
```

### Search

```bash
plume:search {query} {--max-results=10} {--sort= : recency|relevancy} {--format=table}
```

### Timelines

```bash
plume:home {--max-results=10} {--format=table}
plume:timeline {--max-results=10} {--format=table}
plume:mentions {--max-results=10} {--format=table}
```

### Users

```bash
plume:user {id?} {--username= : Look up by username instead of ID} {--format=table}
```

### Likes

```bash
plume:like {id}
plume:unlike {id}
plume:likes {--max-results=10} {--format=table}
```

### Retweets

```bash
plume:retweet {id}
plume:unretweet {id}
```

### Follows

```bash
plume:follow {id}
plume:unfollow {id} {--force}
plume:followers {--max-results=100} {--format=table}
plume:following {--max-results=100} {--format=table}
```

### Bookmarks

```bash
plume:bookmark {id}
plume:unbookmark {id}
plume:bookmarks {--max-results=10} {--format=table}
```

### Blocks

```bash
plume:block {id} {--force}
plume:unblock {id} {--force}
plume:blocked {--max-results=100} {--format=table}
```

### Mutes

```bash
plume:mute {id} {--force}
plume:unmute {id} {--force}
plume:muted {--max-results=100} {--format=table}
```

### Media

```bash
plume:upload {file : Path to media file} {--alt-text=} {--format=table}
```

### Lists

```bash
plume:lists {--max-results=100} {--format=table}
plume:lists:create {name} {--description=} {--private} {--format=table}
plume:lists:get {id} {--format=table}
plume:lists:update {id} {--name=} {--description=} {--format=table}
plume:lists:delete {id} {--force}
plume:lists:members {id} {--max-results=100} {--format=table}
plume:lists:add-member {list-id} {user-id}
plume:lists:remove-member {list-id} {user-id} {--force}
plume:lists:tweets {id} {--max-results=10} {--format=table}
plume:lists:follow {id}
plume:lists:unfollow {id}
plume:lists:pin {id}
plume:lists:unpin {id}
```

## Examples

```bash
# Post a tweet
php artisan plume:post --text="Hello from the CLI!"

# Reply to a tweet
php artisan plume:post --text="Great thread!" --reply-to=1234567890

# Search and get JSON
php artisan plume:search "laravel" --max-results=20 --format=json

# Look up user by username
php artisan plume:user --username=taylorotwell

# Get your home timeline as JSON
php artisan plume:home --max-results=10 --format=json

# Like a tweet
php artisan plume:like 1234567890

# Delete without confirmation
php artisan plume:delete-post 1234567890 --force
```

## Architecture Notes

- Commands use `ResolvesXClient` trait for auth (checks `X_BEARER_TOKEN` config)
- Commands use `SupportsJsonOutput` trait for `--format=json` support
- User-scoped commands call `resolveUserId()` which hits `/me` to get the authenticated user's ID
- All commands registered in `XServiceProvider::boot()` (41 total)
- Token refresh uses container binding `x.token_refreshed` (NOT config)
