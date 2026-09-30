<?php
/**
 * Uninstall cleanup for BetterStack Logger.
 *
 * Removes the plugin's settings. Logs already sent to BetterStack are not
 * affected, and a BETTERSTACK_API_KEY constant in wp-config.php must be
 * removed by hand.
 *
 * @package BetterStackLogger
 */

defined( 'ABSPATH' ) || exit;
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Delete this plugin's options on the current site.
 */
function betterstack_logger_uninstall_site() {
	delete_option( 'betterstack_api_key' );
	delete_option( 'betterstack_error_logging_enabled' );
	delete_option( 'betterstack_event_logging_enabled' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $betterstack_site_id ) {
		switch_to_blog( $betterstack_site_id );
		betterstack_logger_uninstall_site();
		restore_current_blog();
	}
} else {
	betterstack_logger_uninstall_site();
}
