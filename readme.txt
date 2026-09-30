=== BetterStack Logger ===
Contributors: prolificdigital
Tags: betterstack, logger, logging, debug, monitoring
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sends error messages, user actions, post changes and custom log messages from WordPress to BetterStack.

== Description ==

BetterStack Logger sends log entries from your WordPress site to BetterStack.

* **Error logging:** messages passed to `wp_die()`.
* **Event logging:** logins, registrations, profile updates, password resets and user deletions; post saves and deletions; plugin activations and deactivations; theme switches.
* **Custom messages:** call `better_error_log( $message )` or `b_log( $message )` from any code.
* **Settings page:** **Tools > BetterStack Logger** sets the API key, toggles error and event logging, and sends a test message.

The API key can also be defined in `wp-config.php`:
`define( 'BETTERSTACK_API_KEY', 'your-api-key-here' );`

== Installation ==

1. Upload the `betterstack-logger` folder to `/wp-content/plugins/`, or install the release zip from **Plugins > Add New > Upload Plugin**.
2. Activate the plugin and go to **Tools > BetterStack Logger** to enter your API key.
3. To receive automatic updates, add your license key to `wp-config.php`:
   `define( 'PROLIFIC_LICENSE_KEY_BETTERSTACK_LOGGER', 'your-key' );`

== Changelog ==

= 1.1.0 =
* Updates now come from the Prolific Digital license server. Define `PROLIFIC_LICENSE_KEY_BETTERSTACK_LOGGER` to receive them.
* License is GPL-2.0-or-later.
* Requires WordPress 6.5+ and PHP 8.4+.
* Uninstalling removes the plugin's settings.
* Security: requests to BetterStack now verify the TLS certificate (`sslverify` was disabled).
* Security: the source token and logging checkboxes are sanitized when saved.

= 1.0.0 =
* Initial release.
