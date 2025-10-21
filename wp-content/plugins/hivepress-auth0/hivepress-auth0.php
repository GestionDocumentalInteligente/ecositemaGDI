<?php
/**
 * Plugin Name: Cero1 - HivePress Auth0 Integration
 * Plugin URI: https://github.com/gdilatam/cero1-marketplace
 * Description: Auth0 SSO integration for HivePress marketplace. Automatic user creation as Contributors.
 * Version: 1.0.0
 * Author: GDI Latam
 * Author URI: https://gdilatam.org
 * License: MIT
 * Text Domain: hivepress-auth0
 */

defined('ABSPATH') || exit;

// Plugin constants
define('HP_AUTH0_VERSION', '1.0.0');
define('HP_AUTH0_PATH', plugin_dir_path(__FILE__));
define('HP_AUTH0_URL', plugin_dir_url(__FILE__));

// Require dependencies
require_once HP_AUTH0_PATH . 'includes/class-auth0-client.php';
require_once HP_AUTH0_PATH . 'includes/class-user-manager.php';
require_once HP_AUTH0_PATH . 'includes/class-login-handler.php';

/**
 * Initialize plugin
 */
add_action('plugins_loaded', 'hp_auth0_init');
function hp_auth0_init() {
    // Check if Auth0 credentials are set
    if (!getenv('AUTH0_DOMAIN') || !getenv('AUTH0_CLIENT_ID') || !getenv('AUTH0_CLIENT_SECRET')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>Auth0 Integration:</strong> Missing environment variables. Please set AUTH0_DOMAIN, AUTH0_CLIENT_ID, and AUTH0_CLIENT_SECRET.</p></div>';
        });
        return;
    }

    // Initialize login handler
    new HP_Auth0_Login_Handler();
}

/**
 * Redirect WordPress login to Auth0
 */
add_action('login_init', 'hp_auth0_redirect_login');
function hp_auth0_redirect_login() {
    // Don't redirect on logout or if already processing Auth0 callback
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        return;
    }

    if (isset($_GET['auth0_callback'])) {
        return;
    }

    // Redirect to Auth0
    HP_Auth0_Login_Handler::redirect_to_auth0();
}

/**
 * Disable native WordPress registration
 */
add_filter('wp_authenticate_user', 'hp_auth0_disable_native_login', 10, 2);
function hp_auth0_disable_native_login($user, $password) {
    // Allow admin to login natively (check if user has admin role)
    if ($user instanceof WP_User && in_array('administrator', $user->roles)) {
        return $user;
    }

    // Check if user was created via Auth0
    $auth0_id = get_user_meta($user->ID, 'auth0_id', true);

    if (empty($auth0_id)) {
        // Not an Auth0 user, block login
        return new WP_Error('auth0_required', __('Please login with Auth0. Only administrators can use native login.', 'hivepress-auth0'));
    }

    return $user;
}

/**
 * Add Auth0 user info to admin user profile
 */
add_action('show_user_profile', 'hp_auth0_show_user_profile');
add_action('edit_user_profile', 'hp_auth0_show_user_profile');
function hp_auth0_show_user_profile($user) {
    $auth0_id = get_user_meta($user->ID, 'auth0_id', true);

    if (!$auth0_id) {
        return;
    }

    $auth0_profile = get_user_meta($user->ID, 'auth0_profile', true);
    ?>
    <h2><?php _e('Auth0 Information', 'hivepress-auth0'); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php _e('Auth0 ID', 'hivepress-auth0'); ?></th>
            <td><code><?php echo esc_html($auth0_id); ?></code></td>
        </tr>
        <?php if ($auth0_profile && isset($auth0_profile['email_verified'])): ?>
        <tr>
            <th><?php _e('Email Verified', 'hivepress-auth0'); ?></th>
            <td><?php echo $auth0_profile['email_verified'] ? '✅ Yes' : '❌ No'; ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th><?php _e('Last Login', 'hivepress-auth0'); ?></th>
            <td><?php echo esc_html(get_user_meta($user->ID, 'auth0_last_login', true) ?: 'N/A'); ?></td>
        </tr>
    </table>
    <?php
}

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'hp_auth0_activate');
function hp_auth0_activate() {
    // Check requirements
    if (!function_exists('curl_init')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires cURL to be enabled.', 'hivepress-auth0'));
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'hp_auth0_deactivate');
function hp_auth0_deactivate() {
    flush_rewrite_rules();
}
