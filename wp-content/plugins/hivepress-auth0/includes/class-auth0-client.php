<?php
/**
 * Auth0 OAuth Client
 * Handles OAuth 2.0 flow with Auth0
 */

defined('ABSPATH') || exit;

class HP_Auth0_Client {

    private $domain;
    private $client_id;
    private $client_secret;
    private $redirect_uri;

    public function __construct() {
        $this->domain = getenv('AUTH0_DOMAIN');
        $this->client_id = getenv('AUTH0_CLIENT_ID');
        $this->client_secret = getenv('AUTH0_CLIENT_SECRET');
        $this->redirect_uri = getenv('AUTH0_REDIRECT_URI') ?: home_url('/wp-login.php');
    }

    /**
     * Get Auth0 authorization URL
     *
     * @return string
     */
    public function get_authorization_url() {
        $params = [
            'client_id' => $this->client_id,
            'response_type' => 'code',
            'redirect_uri' => $this->redirect_uri,
            'scope' => 'openid profile email',
            'state' => wp_create_nonce('auth0_login'), // CSRF protection
        ];

        return "https://{$this->domain}/authorize?" . http_build_query($params);
    }

    /**
     * Exchange authorization code for tokens
     *
     * @param string $code Authorization code
     * @return array|WP_Error
     */
    public function exchange_code($code) {
        $response = wp_remote_post("https://{$this->domain}/oauth/token", [
            'body' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'code' => $code,
                'redirect_uri' => $this->redirect_uri,
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('auth0_token_error', $data['error_description'] ?? $data['error']);
        }

        return $data;
    }

    /**
     * Get user info from Auth0
     *
     * @param string $access_token
     * @return array|WP_Error
     */
    public function get_user_info($access_token) {
        $response = wp_remote_get("https://{$this->domain}/userinfo", [
            'headers' => [
                'Authorization' => "Bearer {$access_token}",
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('auth0_userinfo_error', $data['error_description'] ?? $data['error']);
        }

        return $data;
    }

    /**
     * Decode JWT token (without validation for now - Auth0 already validated)
     *
     * @param string $token
     * @return array|WP_Error
     */
    public function decode_id_token($token) {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return new WP_Error('invalid_jwt', 'Invalid JWT format');
        }

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        if (!$payload) {
            return new WP_Error('invalid_jwt', 'Invalid JWT payload');
        }

        return $payload;
    }

    /**
     * Get logout URL
     *
     * @return string
     */
    public function get_logout_url() {
        $params = [
            'client_id' => $this->client_id,
            'returnTo' => home_url(),
        ];

        return "https://{$this->domain}/v2/logout?" . http_build_query($params);
    }
}
