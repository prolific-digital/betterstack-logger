<?php

if (!defined('WPINC')) {
  die;
}

/**
 * BetterStackLogger Class
 *
 * This class integrates BetterStack logging with WordPress. It handles logging
 * of various WordPress events, including user actions, post changes, plugin activation,
 * theme switching, and option updates. The class also provides an admin settings page
 * for configuring API keys and enabling/disabling specific logging features.
 *
 * @package BetterStackLogger
 */
class BetterStackLogger {

  /**
   * The BetterStack API key used for logging.
   *
   * @var string|null
   */
  private $api_key;

  /**
   * Constructor for the BetterStackLogger class.
   *
   * Initializes the API key and hooks into various WordPress actions
   * to log events and display an admin settings page.
   */
  public function __construct() {
    $this->api_key = defined('BETTERSTACK_API_KEY') ? BETTERSTACK_API_KEY : get_option('betterstack_api_key');
    add_action('admin_menu', [$this, 'betterstack_logger_menu']);
    add_action('admin_init', [$this, 'settings_init']);
    add_action('wp_die_handler', [$this, 'betterstack_wp_die_handler']);

    if (get_option('betterstack_event_logging_enabled') === 'yes') {
      // User-related events
      add_action('wp_login', [$this, 'log_event'], 10, 2);
      add_action('user_register', [$this, 'log_event']);
      add_action('delete_user', [$this, 'log_event']);
      add_action('profile_update', [$this, 'log_event'], 10, 2);
      add_action('password_reset', [$this, 'log_event'], 10, 2);

      // Post-related events
      // add_action('transition_post_status', [$this, 'log_event'], 10, 3); // Handles both creation and status changes
      add_action('delete_post', [$this, 'log_event']); // Handles post deletion
      add_action('save_post', [$this, 'log_event'], 10, 3); // Handles post creation and updates

      // Plugin-related events
      add_action('activated_plugin', [$this, 'log_event'], 10, 2);
      add_action('deactivated_plugin', [$this, 'log_event'], 10, 2);

      // Theme-related events
      add_action('switch_theme', [$this, 'log_event'], 10, 2);

      // // // General settings updates
      // add_action('updated_option', [$this, 'log_event'], 10, 3);
    }
  }

  /**
   * Logs an event based on the current action.
   *
   * This method is hooked into various WordPress actions to log events
   * such as user logins, post changes, and plugin activations.
   *
   * @param mixed ...$args The arguments passed by the action hook.
   */
  public function log_event(...$args) {
    $event_messages = [
      'wp_login'              => isset($args[0]) ? "User {$args[0]} logged in." : "Login event detected, but user data is missing.",
      'user_register'         => ($user = get_userdata($args[0])) ? "New user registered: {$user->user_login}." : "User registration event detected, but user data is missing.",
      'delete_user'           => ($user = get_userdata($args[0])) ? "User deleted: {$user->user_login}." : "User deletion event detected, but user data is missing.",
      'profile_update'        => ($user = get_userdata($args[0])) ? "User profile updated: {$user->user_login}." : "User profile update detected, but user data is missing.",
      'password_reset'        => isset($args[0]) && is_object($args[0]) ? "User {$args[0]->user_login} reset their password." : "Password reset event detected, but user data is missing.",
      'transition_post_status' => $this->get_post_status_message($args),
      'delete_post'           => ($post = get_post($args[0])) ? "Post deleted: '{$post->post_title}' (ID: {$post->ID})." : "Post deletion event detected, but post data is missing.",
      'save_post'             => $this->get_save_post_message($args),
      'activated_plugin'      => $this->get_plugin_name($args[0]) ? "Plugin activated: " . $this->get_plugin_name($args[0]) . "." : "Plugin activation event detected, but plugin data is missing.",
      'deactivated_plugin'    => $this->get_plugin_name($args[0]) ? "Plugin deactivated: " . $this->get_plugin_name($args[0]) . "." : "Plugin deactivation event detected, but plugin data is missing.",
      'switch_theme'          => isset($args[0]) ? "Theme switched to: {$args[0]}." : "Theme switch event detected, but theme data is missing.",
      'updated_option'        => $this->get_updated_option_message($args),
    ];

    $current_action = current_filter(); // Get the name of the current action
    if (isset($event_messages[$current_action])) {
      $message = $event_messages[$current_action];
      if ($message !== false) {
        $this->log_error($message); // Only log if the message is not false
      }
    }
  }

  /**
   * Retrieves the human-readable name of a plugin.
   *
   * @param string $plugin_path The path to the plugin file.
   * @return string|false The plugin name, or false if the plugin file doesn't exist.
   */
  private function get_plugin_name($plugin_path) {
    // Get the absolute path to the plugin file
    $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_path;

    // Check if the file exists
    if (file_exists($plugin_file)) {
      // Get plugin data and return the Name
      $plugin_data = get_plugin_data($plugin_file);
      return $plugin_data['Name'];
    }

    return false; // Return false if the plugin file does not exist
  }

  /**
   * Generates a log message for an updated option.
   *
   * Skips logging for the 'active_plugins' and 'recently_activated' options.
   *
   * @param array $args The arguments passed by the action hook.
   * @return string|false The log message, or false if the option should not be logged.
   */
  private function get_updated_option_message($args) {
    $option_name = $args[0];

    // Skip logging for specific options
    if (in_array($option_name, ['active_plugins', 'recently_activated'])) {
      return false; // Return false to indicate no logging should occur
    }

    $old_value = maybe_serialize($args[1]); // Handles arrays or objects by serializing them
    $new_value = maybe_serialize($args[2]);

    return "Option '{$option_name}' updated. Old value: '{$old_value}', New value: '{$new_value}'.";
  }

  /**
   * Generates a log message for a post status change.
   *
   * @param array $args The arguments passed by the action hook.
   * @return string The log message.
   */
  private function get_post_status_message($args) {
    // Validate that $args[2] is a valid WP_Post object
    if (!isset($args[2]) || !($post = $args[2]) || !is_a($post, 'WP_Post')) {
      return "Post status change detected, but post data is missing or invalid.";
    }

    $new_status = $args[0];
    $old_status = $args[1];

    // Construct the message based on the status change
    if ($old_status == 'auto-draft' && $new_status == 'publish') {
      return "New post published: '{$post->post_title}' (ID: {$post->ID}).";
    } elseif ($new_status == 'publish') {
      return "Post updated: '{$post->post_title}' (ID: {$post->ID}) changed from '{$old_status}' to '{$new_status}'.";
    }
    return "Post '{$post->post_title}' (ID: {$post->ID}) changed from '{$old_status}' to '{$new_status}'.";
  }

  /**
   * Generates a log message for a post save event.
   *
   * @param array $args The arguments passed by the action hook.
   * @return string The log message.
   */
  private function get_save_post_message($args) {
    // Validate that $args[0] is a valid post ID and get the post
    if (!isset($args[0]) || !($post = get_post($args[0])) || !is_a($post, 'WP_Post')) {
      return "Post save detected, but post data is missing or invalid.";
    }

    $update = $args[2];

    if ($update) {
      return "Post updated: '{$post->post_title}' (ID: {$post->ID}).";
    } else {
      return "New post created: '{$post->post_title}' (ID: {$post->ID}).";
    }
  }

  /**
   * Sends an error message to BetterStack.
   *
   * @param string $message The message to log.
   * @return string The result of the log attempt.
   */
  public function log_error($message) {
    return $this->create_log_entry($message);
  }

  /**
   * Creates a log entry and sends it to BetterStack.
   *
   * @param string $message The message to log.
   * @return string The result of the log attempt.
   */
  private function create_log_entry($message) {
    if (!$this->api_key) {
      return "API key is not set.";
    }

    $url = "https://in.logs.betterstack.com";
    $date = gmdate('Y-m-d H:i:s') . " UTC";

    $data = [
      "dt" => $date,
      "message" => $message
    ];

    $args = [
      'body'        => wp_json_encode($data),
      'headers'     => [
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer ' . $this->api_key,
      ],
      'timeout'     => 15,
      'sslverify'   => false,
    ];

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
      return "Failed to send log message. Error: " . $response->get_error_message();
    }

    $http_code = wp_remote_retrieve_response_code($response);

    if ($http_code == 202) {
      return "Log message sent successfully!";
    } else {
      $response_body = wp_remote_retrieve_body($response);
      return "Failed to send log message. Status code: $http_code, Response: $response_body";
    }
  }


  /**
   * Custom handler for wp_die, logs errors via BetterStack.
   *
   * @return callable The custom wp_die handler function.
   */
  public function betterstack_wp_die_handler() {
    return function ($message, $title = '', $args = []) {
      $error_logging_enabled = get_option('betterstack_error_logging_enabled', 'yes');
      if ($error_logging_enabled === 'yes') {
        $this->log_error($message);
      }
      _default_wp_die_handler($message, $title, $args);
    };
  }

  /**
   * Adds the BetterStack Logger settings menu to the WordPress admin.
   */
  public function betterstack_logger_menu() {
    add_submenu_page('tools.php', 'BetterStack Logger Settings', 'BetterStack Logger', 'manage_options', 'betterstack-logger', [$this, 'settings_page']);
  }

  /**
   * Displays the BetterStack Logger settings page.
   */
  public function settings_page() {
    if (isset($_POST['betterstack_test_message'])) {
      // Verify the nonce before processing the form data
      if (!isset($_POST['betterstack_nonce']) || !wp_verify_nonce($_POST['betterstack_nonce'], 'betterstack_test_message_action')) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Security check failed. Please try again.', 'betterstack-logger') . '</p></div>';
        return;
      }

      // Sanitize and process the form data
      if (!empty($_POST['betterstack_test_message'])) {
        $test_message = sanitize_text_field($_POST['betterstack_test_message']);
        $result = $this->log_error($test_message);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result) . '</p></div>';
      }
    }
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
        <?php wp_nonce_field('betterstack_test_message_action', 'betterstack_nonce'); ?>
        <input type="text" name="betterstack_test_message" value="" placeholder="Enter test message" size="50">
        <?php submit_button('Send Test Message'); ?>
      </form>
    </div>
<?php
  }


  /**
   * Initializes the BetterStack Logger settings.
   *
   * Registers settings and adds settings sections and fields for the API key,
   * error logging, and event logging options.
   */
  public function settings_init() {
    register_setting('betterstack_logger_options_group', 'betterstack_api_key');
    register_setting('betterstack_logger_options_group', 'betterstack_error_logging_enabled');

    register_setting('betterstack_logger_options_group', 'betterstack_event_logging_enabled');

    add_settings_section(
      'betterstack_logger_settings_section',
      'API Settings',
      function () {
        echo '<p class="description">Enter your BetterStack API key and configure logging settings below. You can optionally define the API key in the wp-config.php file using <code>BETTERSTACK_API_KEY</code>.</p>';
        echo '<p class="description">For more information, visit our <a href="https://prolificdigital.notion.site/BetterStack-Logger-c0cc4526efd049c09b77965bf3ecc28e" target="_blank">support article</a>.</p>';
      },
      'betterstack-logger'
    );


    add_settings_field(
      'betterstack_api_key',
      'API Key',
      function () {
        if (defined('BETTERSTACK_API_KEY')) {
          echo '<input type="text" name="betterstack_api_key" value="' . esc_attr(BETTERSTACK_API_KEY) . '" size="50" readonly>';
          echo '<p class="description">This API key is defined in the wp-config.php file and cannot be changed here.</p>';
        } else {
          $api_key = get_option('betterstack_api_key');
          echo '<input type="text" name="betterstack_api_key" value="' . esc_attr($api_key) . '" size="50">';
        }
      },
      'betterstack-logger',
      'betterstack_logger_settings_section'
    );

    add_settings_field(
      'betterstack_error_logging_enabled',
      'Enable Error Logging',
      function () {
        $error_logging_enabled = get_option('betterstack_error_logging_enabled', 'yes');
        echo '<input type="checkbox" name="betterstack_error_logging_enabled" value="yes" ' . checked($error_logging_enabled, 'yes', false) . '> Enable WordPress error logging';
      },
      'betterstack-logger',
      'betterstack_logger_settings_section'
    );

    add_settings_field(
      'betterstack_event_logging_enabled',
      'Enable Event Logging',
      function () {
        $event_logging_enabled = get_option('betterstack_event_logging_enabled', 'no');
        echo '<input type="checkbox" name="betterstack_event_logging_enabled" value="yes" ' . checked($event_logging_enabled, 'yes', false) . '> Enable logging of all user actions on the site';
        echo '<p class="description">This will log user actions and post changes. <a href="https://prolificdigital.notion.site/BetterStack-Logger-c0cc4526efd049c09b77965bf3ecc28e" target="_blank">Learn more</a></p>';
      },
      'betterstack-logger',
      'betterstack_logger_settings_section'
    );
  }
}
