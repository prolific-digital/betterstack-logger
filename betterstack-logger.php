<?php
/*
Plugin Name: BetterStack Logger
Plugin URI:  https://prolificdigital.com
Description: Seamlessly integrate with BetterStack to log messages directly from your WordPress site. Enhance your logging capabilities with ease and precision.
Short Description: Seamlessly integrate with BetterStack to log messages directly from your WordPress site.
Tags: betterstack, logger, logging, error, errors, debug, debugging, log, logs, monitoring, monitoring, betterstack logger, betterstack logging, betterstack error logging, betterstack error logger, betterstack debug logging, betterstack debug logger
Version: 1.0.0
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 6.6
Author: Prolific Digital
Author URI: https://prolificdigital.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: betterstack-logger

*/

if (!defined('WPINC')) {
  die;
}

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
