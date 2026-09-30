<?php
/**
 * Plugin Name:       BetterStack Logger
 * Plugin URI:        https://prolificdigital.com
 * Description:       Sends error messages, user actions, post changes and custom log messages from WordPress to BetterStack.
 * Version:           1.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.4
 * Author:            Prolific Digital
 * Author URI:        https://prolificdigital.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       betterstack-logger
 * Domain Path:       /languages
 * Update URI:        https://api.prolificdigital.io/api/update?plugin=betterstack-logger
 *
 * @package BetterStackLogger
 */

defined( 'ABSPATH' ) || exit;

define( 'BETTERSTACK_LOGGER_VERSION', '1.1.0' );

define( 'BETTERSTACK_LOGGER_PLUGIN_FILE', __FILE__ );
define( 'BETTERSTACK_LOGGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Composer dependencies (plugin-update-checker). Present in release zips;
// in a git checkout run `composer install` first.
if ( is_readable( BETTERSTACK_LOGGER_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once BETTERSTACK_LOGGER_PLUGIN_DIR . 'vendor/autoload.php';
}

require_once BETTERSTACK_LOGGER_PLUGIN_DIR . 'includes/update-checker.php';

/**
 * Load bundled translations from /languages (the header's Domain Path).
 */
add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'betterstack-logger', false, dirname( plugin_basename( BETTERSTACK_LOGGER_PLUGIN_FILE ) ) . '/languages' );
	}
);

require_once plugin_dir_path(__FILE__) . 'includes/BetterStackLogger.php';

// Create a global instance of the BetterStackLogger class
global $betterstack_logger;
$betterstack_logger = new BetterStackLogger();

/**
 * Log an error message using BetterStackLogger.
 *
 * This function allows developers to easily log messages to BetterStack
 * from anywhere in the codebase by calling better_error_log($message).
 *
 * @param string $message The message to log.
 */
if (!function_exists('better_error_log')) {
  function better_error_log($message) {
    global $betterstack_logger;

    if ($betterstack_logger instanceof BetterStackLogger) {
      $betterstack_logger->log_error($message);
    }
  }
}

/**
 * Log an error message using BetterStackLogger.
 *
 * This function provides a shorthand way for developers to log messages to BetterStack
 * from anywhere in the codebase by calling b_log($message).
 *
 * @param string $message The message to log.
 */
if (!function_exists('b_log')) {
  function b_log($message) {
    global $betterstack_logger;

    if ($betterstack_logger instanceof BetterStackLogger) {
      $betterstack_logger->log_error($message);
    }
  }
}
