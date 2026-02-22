# CLAUDE.md

Plume is an X (Twitter) API v2 client for Laravel.

## Stack

PHP 8.2+, Laravel 11/12, Pest v4, PHPStan level 8, Laravel Pint.

## Commands

```bash
composer test              # Run unit + feature tests
composer test:integration  # Run integration tests (requires API credentials)
composer test:all          # Run all tests
composer phpstan           # Static analysis (level 8)
composer lint              # Fix code style with Pint
composer lint:check        # Check code style without fixing
```

## Architecture

- **Contract**: `Plume\Contracts\XApiProvider` defines the full API surface
- **Client**: `Plume\XApiClient` implements the contract via 12 concern traits + `MapsApiResponses`
- **HTTP**: `Plume\Http\XHttpClient` handles auth, token refresh, error mapping
- **Facade**: `Plume\Facades\X` proxies to `XApiProvider` binding
- **DTOs**: `Plume\Data\` namespace -- `Post`, `User`, `XList`, `Media`, `Place`, `Poll`, `PaginatedResult`, `Includes`, metrics classes
- **Scoped Client**: `Plume\ScopedXClient` wraps the client for user-context operations, auto-resolving userId via `me()`
- **Enums**: `Plume\Enums\` -- typed field selectors (`TweetField`, `UserField`, `Expansion`, etc.)
- **Exceptions**: `XApiException` base, `RateLimitException`, `AuthenticationException`, `ValidationException`
- **AI Tools**: 15 tools in `Plume\Ai\Tools\` implementing `Laravel\Ai\Contracts\Tool`, tagged as `ai-tools`
- **Testing**: `X::fake()` returns `FakeXApiProvider` with call recording and semantic assertions

## Conventions

- `declare(strict_types=1)` in all PHP files
- PHPStan level 8 must pass
- Pint with `laravel` preset
- Concern traits for API domain grouping (ManagesPosts, ManagesSearch, etc.)
- DTOs use constructor property promotion with `readonly` properties
- Active Record methods on DTOs require `withProvider()` to be called first
- Config key `x`, facade alias `X`, env prefix `X_`
