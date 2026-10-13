# Changelog

All notable changes to this project will be documented in this file.

## Unreleased

### Fixed

- Includes models with inherited or composed `UseQueryString` traits in the cache and excludes non-instantiable models.
- Replaces the filter cache atomically and preserves the previous cache if generation fails.

## v2.2.0

Released on _**2026-10-10**_.

### Added

- Automatic filtering from JSON or form request bodies for the HTTP `QUERY` method.

### Changed

- Requires Symfony HttpFoundation ^7.4 or ^8.0 for HTTP `QUERY` body parsing.

### Fixed

- Corrects the visibility of `getQueryStringObject()` in the README example.

## v2.1.0

Released on _**2026-04-02**_.

### Added

- Adds variadic argument on QueryString attribute.

## v2.0.0

Released on _**2026-03-22**_.

### Added

- Laravel 13 support.

## v1.1.1

Released on _**2025-07-30**_.

### Added

- Badges. ✨

### Changed

- Updates dependencies.

## v1.1.0

Released on _**2025-07-04**_.

### Added

- Query string caching.
- Artisan commands "querystring:cache" and "querystring:clear".
- Service QueryString.

## v1.0.1

Released on _**2025-06-20**_.

### Added

- PHPStan, Larastan.
- Laravel Pint, CS Fixer.
- Requires illuminate/database, illuminate/http.

## v1.0.0

Released on _**2025-02-14**_.

### Added

- Custom filter object via `getQueryStringObject()` method.

### Removed

- `normalizeQueryStringValue()` removed, use default Laravel middleware.

## v0.1.1

Released on _**2025-02-09**_.

### Added

- Improved README.md
- Adds CHANGELOG.md

### Fixed

- Wrong path when publishing the configuration file.

## v0.1.0

Initial release.
