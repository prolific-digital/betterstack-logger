# BetterStack Logger

BetterStack Logger is a powerful WordPress plugin that allows you to seamlessly integrate BetterStack with your WordPress site. Enhance your logging capabilities by sending error messages, post changes, user actions, and more directly to BetterStack. With easy configuration and flexible logging functions, this plugin is an essential tool for developers and site administrators looking to keep track of their site's activities.

| | |
|---|---|
| Requires | WordPress 6.5+, PHP 8.4+ |
| Text domain | `betterstack-logger` |
| License | GPL-2.0-or-later |

## Features

- **Error Logging:** Capture and log WordPress errors directly to BetterStack.
- **User Action Logging:** Log important user actions such as logins, registrations, and profile updates.
- **Post Changes:** Monitor post creation, updates, and deletions.
- **Plugin and Theme Activity:** Log plugin activations/deactivations and theme switches.
- **Customizable Logging:** Use helper functions to log custom messages from anywhere in your codebase.
- **Settings Page:** Easily configure the API key, enable or disable specific logging features via the WordPress admin panel.
- **Support for `wp-config.php`:** Define your BetterStack API key in `wp-config.php` for additional security.

## Installation

1. Download the plugin from the [Plugin Releases](https://github.com/prolific-digital/betterstack-logger/releases).
2. Upload the `betterstack-logger` directory to the `/wp-content/plugins/` directory.
3. Activate the plugin through the `Plugins` menu in WordPress.
4. Go to `Tools` -> `BetterStack Logger Settings` to configure the plugin.

## Configuration

### API Key

To log messages to BetterStack, you need to set up your API key:

1. Go to `Tools` -> `BetterStack Logger Settings`.
2. Enter your BetterStack API key in the `API Key` field.
3. Optionally, define the API key in your `wp-config.php` file using `BETTERSTACK_API_KEY`.

### Logging Options

- **Enable Error Logging:** Toggle to capture WordPress errors.
- **Enable Event Logging:** Toggle to log user actions, post changes, and more.

## Usage

### Logging Custom Messages

Use the following functions to log messages from anywhere in your code:

```php
// Log a custom message
better_error_log('This is a custom error message.');

// Shorter version
b_log('This is a quick log message.');
```

### Example:

```php
function my_custom_function() {
    // Perform some task
    // ...

    // Log a message
    better_error_log('Task completed successfully.');
}

add_action('init', 'my_custom_function');
```

### Available Functions

- `better_error_log($message)`: Logs a custom message to BetterStack.
- `b_log($message)`: Shorthand function to log a custom message to BetterStack.

## Frequently Asked Questions

Can I define the API key in `wp-config.php`?

Yes! For added security, you can define your API key in `wp-config.php` using:

```php
define('BETTERSTACK_API_KEY', 'your-api-key-here');
```

What types of events can I log with BetterStack Logger?

You can log errors, user actions (e.g., logins, registrations), post changes (e.g., creation, updates, deletions), plugin activations/deactivations, theme switches, and custom messages.

## Updates

Updates are served by the Prolific Digital license server. Add the site's license key to `wp-config.php`:

```php
define( 'PROLIFIC_LICENSE_KEY_BETTERSTACK_LOGGER', 'your-key' );
```

You can also supply the key with the `prolific_license_key` filter, which receives `( $key, 'betterstack-logger' )`.

## Development

The dev site is the DDEV sandbox at `https://wp-sandbox.ddev.site`. Run commands in the container:

```sh
ddev exec -d /var/www/html/wp-content/plugins/betterstack-logger composer install
ddev exec -d /var/www/html/wp-content/plugins/betterstack-logger composer lint    # php -l on every file
ddev exec -d /var/www/html/wp-content/plugins/betterstack-logger composer phpcs   # WordPress-Extra + PHPCompatibilityWP
```

`vendor/` is not committed. A git checkout needs `composer install` before the updater loads. The plugin itself still runs without it.

## Releasing

1. Bump the version in the plugin header, `BETTERSTACK_LOGGER_VERSION` and `readme.txt` `Stable tag`.
2. Add a `## [x.y.z] - YYYY-MM-DD` section to `CHANGELOG.md` and a `= x.y.z =` entry to `readme.txt`.
3. Merge to `main`, then push a `vx.y.z` tag. The release workflow builds `betterstack-logger.zip` (with `composer install --no-dev`, honoring `.distignore`) and attaches it to a GitHub Release.

CI and release logic live in the shared workflows in [`prolific-digital/.github`](https://github.com/prolific-digital/.github).
## Stored data

| Key | Type | Purpose |
|---|---|---|
| `betterstack_api_key` | option | BetterStack source token (unless `BETTERSTACK_API_KEY` is defined) |
| `betterstack_error_logging_enabled` | option | `yes` to log `wp_die()` errors |
| `betterstack_event_logging_enabled` | option | `yes` to log user, post, plugin and theme events |

Uninstalling deletes all three options.

## Support

If you need help or have any questions, feel free to reach out to us:

- Visit our [Support Page](https://prolificdigital.notion.site/BetterStack-Logger-c0cc4526efd049c09b77965bf3ecc28e)
- Email us at support@prolificdigital.com
