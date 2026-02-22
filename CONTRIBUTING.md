# Contributing

Thank you for considering contributing to Plume.

## Development Setup

```bash
git clone https://github.com/jkudish/plume.git
cd plume
composer install
```

## Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feat/my-feature`)
3. Make your changes
4. Run the checks:
   ```bash
   composer test          # Run tests
   composer phpstan       # Static analysis
   composer lint          # Fix code style
   composer lint:check    # Check code style without fixing
   ```
5. Commit your changes
6. Push to your fork and submit a pull request

## Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) with the `laravel` preset. Run `composer lint` before submitting.

## Tests

All new features and bug fixes must include tests. This project uses [Pest](https://pestphp.com/).

```bash
composer test    # Run the full test suite
```

## Static Analysis

PHPStan level 8 must pass:

```bash
composer phpstan
```

## Pull Requests

- Keep PRs focused on a single change
- Include tests for new functionality
- Ensure all checks pass before requesting review
- Reference any related issues in the PR description
