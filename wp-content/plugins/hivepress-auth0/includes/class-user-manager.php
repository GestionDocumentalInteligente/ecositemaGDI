<?php
/**
 * Auth0 User Manager
 * Handles WordPress user creation and synchronization
 */

defined('ABSPATH') || exit;

class HP_Auth0_User_Manager {

    /**
     * Find or create WordPress user from Auth0 data
     *
     * @param array $auth0_user Auth0 user profile
     * @return WP_User|WP_Error
     */
    public static function find_or_create_user($auth0_user) {
        // Validate required fields
        if (empty($auth0_user['sub']) || empty($auth0_user['email'])) {
            return new WP_Error('invalid_user_data', 'Missing required Auth0 user data (sub or email)');
        }

        // Search by auth0_id
        $users = get_users([
            'meta_key' => 'auth0_id',
            'meta_value' => $auth0_user['sub'],
            'number' => 1,
        ]);

        if (!empty($users)) {
            $user = $users[0];
            self::update_user_metadata($user->ID, $auth0_user);
            return $user;
        }

        // Search by email (in case user existed before)
        $user = get_user_by('email', $auth0_user['email']);

        if ($user) {
            // Link existing user to Auth0
            update_user_meta($user->ID, 'auth0_id', $auth0_user['sub']);
            update_user_meta($user->ID, 'auth0_profile', $auth0_user);
            self::update_user_metadata($user->ID, $auth0_user);
            return $user;
        }

        // Create new user
        return self::create_user($auth0_user);
    }

    /**
     * Create new WordPress user from Auth0 data
     *
     * @param array $auth0_user
     * @return WP_User|WP_Error
     */
    private static function create_user($auth0_user) {
        // Generate username from email or nickname
        $username = sanitize_user($auth0_user['nickname'] ?? self::generate_username_from_email($auth0_user['email']));

        // Ensure username is unique
        $username = self::ensure_unique_username($username);

        // Create user
        $user_id = wp_create_user(
            $username,
            wp_generate_password(32, true, true), // Random strong password
            $auth0_user['email']
        );

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Set user role to Contributor
        $user = new WP_User($user_id);
        $user->set_role('contributor');

        // Update user data
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $auth0_user['name'] ?? $auth0_user['nickname'] ?? $auth0_user['email'],
            'first_name' => $auth0_user['given_name'] ?? '',
            'last_name' => $auth0_user['family_name'] ?? '',
        ]);

        // Store Auth0 metadata
        update_user_meta($user_id, 'auth0_id', $auth0_user['sub']);
        update_user_meta($user_id, 'auth0_profile', $auth0_user);
        update_user_meta($user_id, 'auth0_created_at', current_time('mysql'));
        self::update_user_metadata($user_id, $auth0_user);

        // Trigger action for extensibility
        do_action('hp_auth0_user_created', $user_id, $auth0_user);

        return $user;
    }

    /**
     * Update user metadata on each login
     *
     * @param int $user_id
     * @param array $auth0_user
     */
    private static function update_user_metadata($user_id, $auth0_user) {
        update_user_meta($user_id, 'auth0_last_login', current_time('mysql'));
        update_user_meta($user_id, 'auth0_profile', $auth0_user);

        // Update profile picture if available
        if (!empty($auth0_user['picture'])) {
            update_user_meta($user_id, 'auth0_picture', $auth0_user['picture']);
        }

        // Trigger action
        do_action('hp_auth0_user_updated', $user_id, $auth0_user);
    }

    /**
     * Generate username from email
     *
     * @param string $email
     * @return string
     */
    private static function generate_username_from_email($email) {
        $parts = explode('@', $email);
        return sanitize_user($parts[0]);
    }

    /**
     * Ensure username is unique by appending numbers
     *
     * @param string $username
     * @return string
     */
    private static function ensure_unique_username($username) {
        $original = $username;
        $counter = 1;

        while (username_exists($username)) {
            $username = $original . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Check if user was created via Auth0
     *
     * @param int|WP_User $user
     * @return bool
     */
    public static function is_auth0_user($user) {
        if (is_numeric($user)) {
            $user_id = $user;
        } elseif ($user instanceof WP_User) {
            $user_id = $user->ID;
        } else {
            return false;
        }

        $auth0_id = get_user_meta($user_id, 'auth0_id', true);
        return !empty($auth0_id);
    }

    /**
     * Get Auth0 profile for user
     *
     * @param int $user_id
     * @return array|null
     */
    public static function get_auth0_profile($user_id) {
        $profile = get_user_meta($user_id, 'auth0_profile', true);
        return is_array($profile) ? $profile : null;
    }
}
