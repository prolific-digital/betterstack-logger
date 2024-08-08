<?php
/*
Plugin Name: BetterStack Logger
Plugin URI:  https://prolificdigital.com
Description: Seamlessly integrate with BetterStack to log messages directly from your WordPress site. Enhance your logging capabilities with ease and precision.
Version:     1.2
Author:      Prolific Digital
Author URI:  https://prolificdigital.com
License:     GPL2
*/

if (!defined('WPINC')) {
  die;
}

function bs_log_error($message) {
  bs_create_log_entry($message);
}

function bs_create_log_entry($message) {
  $api_key = get_option('betterstack_api_key');
  if (!$api_key) {
    return "API key is not set.";
  }

  $url = "https://in.logs.betterstack.com";
  $date = gmdate('Y-m-d H:i:s') . " UTC";

  $data = json_encode([
    "dt" => $date,
    "message" => $message
  ]);

  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  curl_close($ch);

  if ($http_code == 202) {
    return "Log message sent successfully!";
  } else {
    return "Failed to send log message. Status code: $http_code, Response: $response";
  }
}

// Send WordPress errors to BetterStack
function betterstack_log_errors($error) {
  $error_logging_enabled = get_option('betterstack_error_logging_enabled', 'yes');
  if ($error_logging_enabled === 'yes') {
    $error_message = sprintf(
      'Error: %s in %s on line %d',
      $error->get_error_message(),
      $error->get_error_file(),
      $error->get_error_line()
    );
    bs_create_log_entry($error_message);
  }
}

add_action('wp_die_handler', 'betterstack_wp_die_handler');
function betterstack_wp_die_handler() {
  return function ($message, $title = '', $args = []) {
    $error_logging_enabled = get_option('betterstack_error_logging_enabled', 'yes');
    if ($error_logging_enabled === 'yes') {
      bs_create_log_entry($message);
    }
    _default_wp_die_handler($message, $title, $args);
  };
}

// Add the plugin settings menu
add_action('admin_menu', 'betterstack_logger_menu');
function betterstack_logger_menu() {
  add_submenu_page('tools.php', 'BetterStack Logger Settings', 'BetterStack Logger', 'manage_options', 'betterstack-logger', 'betterstack_logger_settings_page');
}

function betterstack_logger_settings_page() {
?>
  <div class="wrap">
    <h1>BetterStack Logger Settings</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('betterstack_logger_options_group');
      do_settings_sections('betterstack-logger');
      submit_button();
      ?>
    </form>
    <h2>Send Test Message</h2>
    <form method="post" action="">
      <input type="text" name="betterstack_test_message" value="" placeholder="Enter test message" size="50">
      <?php submit_button('Send Test Message'); ?>
    </form>
    <?php
    if (isset($_POST['betterstack_test_message']) && !empty($_POST['betterstack_test_message'])) {
      $test_message = sanitize_text_field($_POST['betterstack_test_message']);
      $result = bs_create_log_entry($test_message);
      echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result) . '</p></div>';
    }
    ?>
  </div>
<?php
}

// Initialize the settings
add_action('admin_init', 'betterstack_logger_settings_init');
function betterstack_logger_settings_init() {
  register_setting('betterstack_logger_options_group', 'betterstack_api_key');
  register_setting('betterstack_logger_options_group', 'betterstack_error_logging_enabled');

  add_settings_section(
    'betterstack_logger_settings_section',
    'API Settings',
    'betterstack_logger_settings_section_callback',
    'betterstack-logger'
  );

  add_settings_field(
    'betterstack_api_key',
    'API Key',
    'betterstack_api_key_render',
    'betterstack-logger',
    'betterstack_logger_settings_section'
  );

  add_settings_section(
    'betterstack_logger_advanced_settings_section',
    'Advanced Settings',
    'betterstack_logger_advanced_settings_section_callback',
    'betterstack-logger'
  );

  add_settings_field(
    'betterstack_error_logging_enabled',
    'Enable Error Logging',
    'betterstack_error_logging_enabled_render',
    'betterstack-logger',
    'betterstack_logger_advanced_settings_section'
  );
}

function betterstack_logger_settings_section_callback() {
  echo 'Enter your BetterStack API key below:';
}

function betterstack_api_key_render() {
  $api_key = get_option('betterstack_api_key');
?>
  <input type="text" name="betterstack_api_key" value="<?php echo esc_attr($api_key); ?>" size="50">
<?php
}

function betterstack_logger_advanced_settings_section_callback() {
  echo 'Configure advanced settings below:';
}

function betterstack_error_logging_enabled_render() {
  $error_logging_enabled = get_option('betterstack_error_logging_enabled', 'yes');
?>
  <input type="checkbox" name="betterstack_error_logging_enabled" value="yes" <?php checked($error_logging_enabled, 'yes'); ?>>
  Enable WordPress error logging
<?php
}
