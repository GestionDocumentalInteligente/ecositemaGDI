<?php
/**
 * Auth0 Login Handler
 * Handles OAuth callback and user login
 */

defined('ABSPATH') || exit;

class HP_Auth0_Login_Handler {

    private $client;

    public function __construct() {
        $this->client = new HP_Auth0_Client();

        // Handle callback
        add_action('init', [$this, 'handle_callback']);

        // Modify logout to go through Auth0
        add_filter('logout_url', [$this, 'modify_logout_url'], 10, 2);
    }

    /**
     * Redirect to Auth0 authorization
     */
    public static function redirect_to_auth0() {
        // Don't redirect if already logged in
        if (is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }

        // Don't redirect if processing callback
        if (isset($_GET['auth0_callback'])) {
            return;
        }

        // Don't redirect AJAX requests
        if (wp_doing_ajax()) {
            return;
        }

        // Don't redirect if explicitly bypassed (for admin native login)
        if (isset($_GET['native_login'])) {
            return;
        }

        $client = new HP_Auth0_Client();
        wp_redirect($client->get_authorization_url());
        exit;
    }

    /**
     * Handle Auth0 callback
     */
    public function handle_callback() {
        // Check if this is an Auth0 callback
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            return;
        }

        // Verify nonce (CSRF protection)
        if (!wp_verify_nonce($_GET['state'], 'auth0_login')) {
            wp_die(__('Security check failed. Please try logging in again.', 'hivepress-auth0'));
        }

        $code = sanitize_text_field($_GET['code']);

        // Exchange code for tokens
        $tokens = $this->client->exchange_code($code);

        if (is_wp_error($tokens)) {
            wp_die(sprintf(
                __('Auth0 authentication failed: %s', 'hivepress-auth0'),
                $tokens->get_error_message()
            ));
        }

        // Get user info
        $access_token = $tokens['access_token'] ?? null;

        if (!$access_token) {
            wp_die(__('Failed to get access token from Auth0.', 'hivepress-auth0'));
        }

        $user_info = $this->client->get_user_info($access_token);

        if (is_wp_error($user_info)) {
            wp_die(sprintf(
                __('Failed to get user info: %s', 'hivepress-auth0'),
                $user_info->get_error_message()
            ));
        }

        // Find or create user
        $user = HP_Auth0_User_Manager::find_or_create_user($user_info);

        if (is_wp_error($user)) {
            wp_die(sprintf(
                __('Failed to create user: %s', 'hivepress-auth0'),
                $user->get_error_message()
            ));
        }

        // Log user in
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);

        // Trigger login action
        do_action('wp_login', $user->user_login, $user);
        do_action('hp_auth0_user_login', $user->ID, $user_info);

        // Redirect to dashboard or home
        $redirect_to = $this->get_redirect_url();
        wp_safe_redirect($redirect_to);
        exit;
    }

    /**
     * Get redirect URL after login
     *
     * @return string
     */
    private function get_redirect_url() {
        // Check if redirect_to parameter was passed
        if (isset($_GET['redirect_to'])) {
            return esc_url_raw($_GET['redirect_to']);
        }

        // Default redirect based on user role
        if (current_user_can('edit_posts')) {
            return admin_url();
        }

        return home_url();
    }

    /**
     * Modify logout URL to go through Auth0
     *
     * @param string $logout_url
     * @param string $redirect
     * @return string
     */
    public function modify_logout_url($logout_url, $redirect) {
        // Get current user
        $user = wp_get_current_user();

        // If Auth0 user, redirect to Auth0 logout
        if (HP_Auth0_User_Manager::is_auth0_user($user)) {
            return $this->client->get_logout_url();
        }

        return $logout_url;
    }

    /**
     * Add native login link for admins
     */
    public static function render_native_login_link() {
        if (isset($_GET['native_login'])) {
            return; // Already on native login
        }

        $native_login_url = add_query_arg('native_login', '1', wp_login_url());
        ?>
        <p style="text-align: center; margin-top: 20px;">
            <a href="<?php echo esc_url($native_login_url); ?>" style="font-size: 12px; color: #666;">
                <?php _e('Admin? Login with WordPress credentials', 'hivepress-auth0'); ?>
            </a>
        </p>
        <?php
    }
}

// Add native login link to login form
add_action('login_form', [HP_Auth0_Login_Handler::class, 'render_native_login_link']);
