<?php
/*
Plugin Name: BetterStack Logger
Plugin URI:  https://prolificdigital.com
Description: Seamlessly integrate with BetterStack to log messages directly from your WordPress site. Enhance your logging capabilities with ease and precision.
Version:     1.0.0
Author:      Prolific Digital
Author URI:  https://prolificdigital.com
License:     GPL2
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
