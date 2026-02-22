# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-02-23

### Added

- 41 artisan commands for full CLI access to the X API (`plume:me`, `plume:post`, `plume:search`, `plume:home`, etc.)
- `ResolvesXClient` trait for command authentication
- `SupportsJsonOutput` trait — `--format=json` support across all list/read commands
- Banner graphic for README
- `.claude/skills/plume-usage.md` skill documentation for Claude Code users

### Changed

- Updated README with artisan commands section, banner graphic, and improved intro
- Updated `composer.json` keywords for better Packagist discoverability
- Fixed `CONTRIBUTING.md` test command references

## [1.0.0] - 2026-02-22

### Added

- Full X API v2 coverage: posts, timelines, search, users, likes, retweets, bookmarks, follows, blocks, mutes, lists, media upload
- `X` facade with complete IDE autocomplete via PHPDoc
- Typed DTOs: `Post`, `User`, `XList`, `Media`, `Place`, `Poll`, `PaginatedResult`, `Includes`
- Active Record-style methods on DTOs (`$post->like()`, `$post->reply()`, `$user->follow()`)
- `ScopedXClient` for user-scoped operations with automatic user ID resolution
- `withUser()` on `ScopedXClient` to inject a user ID or `User` DTO directly, skipping the `/me` API call
- `HasXCredentials` contract for user model integration
- OAuth 2.0 with automatic token refresh and configurable callback
- Built-in cursor pagination via `PaginatedResult::nextPage()`
- 15 AI tools for Laravel AI SDK integration
- `X::fake()` test double with semantic assertions
- Typed enums for all API field parameters
- Exception hierarchy: `XApiException`, `RateLimitException`, `AuthenticationException`, `ValidationException`
- PHPStan level 8 compliance
- Pest v4 test suite

[1.1.0]: https://github.com/jkudish/plume/releases/tag/v1.1.0
[1.0.0]: https://github.com/jkudish/plume/releases/tag/v1.0.0
