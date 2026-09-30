# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-09-29

### Changed
- Adopted the Prolific plugin standard. Updates now come from the Prolific Digital license server (`api.prolificdigital.io`). Set `PROLIFIC_LICENSE_KEY_BETTERSTACK_LOGGER` in `wp-config.php` (or use the `prolific_license_key` filter) to receive updates.
- License is GPL-2.0-or-later (the bundled LICENSE file was GPL-3.0 text).
- Requires WordPress 6.5+ and PHP 8.4+.

### Added
- `BETTERSTACK_LOGGER_VERSION` constant and translation loading from `languages/`.
- `uninstall.php` removes the plugin's three options.
- `CHANGELOG.md`, coding-standards config and CI/release workflows.

## [1.0.0] - 2024-08-10

### Added
- Initial release: error, user, post, plugin and theme event logging to BetterStack, `better_error_log()` and `b_log()` helpers, and a **Tools > BetterStack Logger** settings page with a test message form.

[Unreleased]: https://github.com/prolific-digital/betterstack-logger/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/prolific-digital/betterstack-logger/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/prolific-digital/betterstack-logger/releases/tag/v1.0.0
