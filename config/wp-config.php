<?php
/**
 * Cero1 - Marketplace de Soluciones para Ciudades
 * WordPress Configuration File
 *
 * All settings are loaded from environment variables
 * DO NOT hardcode sensitive data here
 */

// Database settings (Railway auto-injects these)
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'wordpress');
define('DB_USER', getenv('MYSQLUSER') ?: 'wordpress');
define('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: 'wordpress');
define('DB_HOST', getenv('MYSQLHOST') ? getenv('MYSQLHOST') . ':' . getenv('MYSQLPORT') : 'db:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// WordPress URLs
define('WP_HOME', getenv('WP_HOME') ?: 'http://localhost:8080');
define('WP_SITEURL', getenv('WP_SITEURL') ?: 'http://localhost:8080');

// Force HTTPS if in production
if (getenv('WP_ENV') === 'production') {
    $_SERVER['HTTPS'] = 'on';
    define('FORCE_SSL_ADMIN', true);
}

// Security Keys and Salts
define('AUTH_KEY',         getenv('AUTH_KEY'));
define('SECURE_AUTH_KEY',  getenv('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY',    getenv('LOGGED_IN_KEY'));
define('NONCE_KEY',        getenv('NONCE_KEY'));
define('AUTH_SALT',        getenv('AUTH_SALT'));
define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT',   getenv('LOGGED_IN_SALT'));
define('NONCE_SALT',       getenv('NONCE_SALT'));

// Database Table Prefix
$table_prefix = 'wp_';

// WordPress Debug Mode
define('WP_DEBUG', getenv('WP_DEBUG') === 'true');
define('WP_DEBUG_LOG', getenv('WP_DEBUG_LOG') === 'true');
define('WP_DEBUG_DISPLAY', getenv('WP_DEBUG_DISPLAY') === 'true');

// Memory Limits
define('WP_MEMORY_LIMIT', getenv('WP_MEMORY_LIMIT') ?: '256M');
define('WP_MAX_MEMORY_LIMIT', getenv('WP_MAX_MEMORY_LIMIT') ?: '512M');

// Post Revisions
define('WP_POST_REVISIONS', intval(getenv('WP_POST_REVISIONS') ?: 5));

// Auto-updates (disable for Railway deploy control)
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);

// File Editor (disable for security)
define('DISALLOW_FILE_EDIT', true);

// Uploads
@ini_set('upload_max_filesize', getenv('UPLOAD_MAX_FILESIZE') ?: '5M');
@ini_set('post_max_size', getenv('POST_MAX_SIZE') ?: '10M');
@ini_set('max_execution_time', getenv('MAX_EXECUTION_TIME') ?: '300');

// Disable cron (use Railway cron if needed)
define('DISABLE_WP_CRON', false);

// Absolute path to WordPress directory
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Load WordPress
require_once ABSPATH . 'wp-settings.php';
