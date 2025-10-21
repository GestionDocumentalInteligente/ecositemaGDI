<?php
/**
 * Cero1 - Must-Use Plugin
 * Global configuration and tweaks
 *
 * This file is automatically loaded by WordPress
 */

defined('ABSPATH') || exit;

// ============================================
// 1. PERFORMANCE TWEAKS
// ============================================

// Limit post revisions
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 5);
}

// Disable WordPress auto-updates (managed by Railway)
add_filter('automatic_updater_disabled', '__return_true');
add_filter('auto_update_core', '__return_false');
add_filter('auto_update_plugin', '__return_false');
add_filter('auto_update_theme', '__return_false');

// ============================================
// 2. SECURITY HARDENING
// ============================================

// Remove WordPress version from header
remove_action('wp_head', 'wp_generator');

// Remove WLW manifest
remove_action('wp_head', 'wlwmanifest_link');

// Remove RSD link
remove_action('wp_head', 'rsd_link');

// Disable XML-RPC (if not needed)
add_filter('xmlrpc_enabled', '__return_false');

// Disable file editing from admin
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

// ============================================
// 3. CLEANUP WP_HEAD
// ============================================

// Remove emoji scripts
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

// ============================================
// 4. CUSTOM BRANDING
// ============================================

// Change login logo
add_action('login_head', 'cero1_custom_login_logo');
function cero1_custom_login_logo() {
    ?>
    <style>
        #login h1 a {
            background-image: none;
            width: auto;
            height: auto;
            font-size: 32px;
            font-weight: bold;
            color: #0A2463;
            text-indent: 0;
        }
        #login h1 a::after {
            content: "Cero1";
        }
        .login form {
            border: 2px solid #0A2463;
        }
        .wp-core-ui .button-primary {
            background: #0A2463;
            border-color: #0A2463;
        }
        .wp-core-ui .button-primary:hover {
            background: #3E92CC;
            border-color: #3E92CC;
        }
    </style>
    <?php
}

// Change login logo URL
add_filter('login_headerurl', function() {
    return home_url();
});

// Change login logo title
add_filter('login_headertext', function() {
    return 'Cero1 - Marketplace de Soluciones para Ciudades';
});

// ============================================
// 5. ADMIN FOOTER TEXT
// ============================================

add_filter('admin_footer_text', 'cero1_admin_footer');
function cero1_admin_footer() {
    return 'Cero1 - Marketplace de Soluciones para Ciudades | Desarrollado con ❤️ por GDI Latam';
}

// ============================================
// 6. DISABLE COMMENTS GLOBALLY
// ============================================

// Disable support for comments
add_action('admin_init', function() {
    // Remove comments from all post types
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function() {
    remove_menu_page('edit-comments.php');
});

// ============================================
// 7. CUSTOM POST TYPE SETTINGS
// ============================================

// Ensure hp_listing is public and searchable
add_filter('register_post_type_args', 'cero1_modify_listing_args', 10, 2);
function cero1_modify_listing_args($args, $post_type) {
    if ($post_type === 'hp_listing') {
        $args['public'] = true;
        $args['publicly_queryable'] = true;
        $args['show_in_rest'] = true; // Enable Gutenberg
        $args['supports'][] = 'thumbnail';
        $args['supports'][] = 'excerpt';
    }
    return $args;
}

// ============================================
// 8. IMAGE COMPRESSION
// ============================================

// Set JPEG quality to 85% (balance quality/size)
add_filter('jpeg_quality', function() {
    return 85;
});

add_filter('wp_editor_set_quality', function() {
    return 85;
});

// ============================================
// 9. CUSTOM EXCERPT LENGTH
// ============================================

add_filter('excerpt_length', function($length) {
    return 30; // 30 words for excerpts
}, 999);

// ============================================
// 10. HTACCESS SECURITY HEADERS
// ============================================

add_action('send_headers', 'cero1_security_headers');
function cero1_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}

// ============================================
// 11. FORCE HTTPS IN PRODUCTION
// ============================================

if (getenv('WP_ENV') === 'production') {
    // Force HTTPS
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https') {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// ============================================
// 12. CUSTOM DASHBOARD WIDGET
// ============================================

add_action('wp_dashboard_setup', 'cero1_dashboard_widget');
function cero1_dashboard_widget() {
    wp_add_dashboard_widget(
        'cero1_info',
        'Cero1 - Información del Sistema',
        'cero1_dashboard_widget_content'
    );
}

function cero1_dashboard_widget_content() {
    $listing_count = wp_count_posts('hp_listing');
    $pending_count = $listing_count->pending ?? 0;
    $published_count = $listing_count->publish ?? 0;

    echo '<ul>';
    echo '<li><strong>Soluciones Publicadas:</strong> ' . $published_count . '</li>';
    echo '<li><strong>Pendientes de Aprobación:</strong> ' . $pending_count . '</li>';
    echo '<li><strong>Auth0 Domain:</strong> ' . getenv('AUTH0_DOMAIN') . '</li>';
    echo '<li><strong>Environment:</strong> ' . (getenv('WP_ENV') ?: 'development') . '</li>';
    echo '</ul>';
}

// ============================================
// END OF MU-PLUGIN
// ============================================
