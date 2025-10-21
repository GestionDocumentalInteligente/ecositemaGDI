<?php
/**
 * Cero1 - Marketplace Child Theme Functions
 *
 * @package Cero1
 */

defined('ABSPATH') || exit;

// ============================================
// 1. ENQUEUE STYLES
// ============================================

add_action('wp_enqueue_scripts', 'cero1_enqueue_styles', 20);
function cero1_enqueue_styles() {
    // Parent theme style
    wp_enqueue_style('hivepress-parent', get_template_directory_uri() . '/style.css');

    // Child theme style
    wp_enqueue_style('cero1-child', get_stylesheet_uri(), ['hivepress-parent'], '1.0.0');
}

// ============================================
// 2. HIVEPRESS CUSTOM FIELDS
// ============================================

add_filter('hivepress/v1/models/listing', 'cero1_add_custom_fields');
function cero1_add_custom_fields($model) {

    // Website (required)
    $model['fields']['website'] = [
        'label' => esc_html__('Website', 'hivepress-child'),
        'description' => esc_html__('Official website URL', 'hivepress-child'),
        'type' => 'url',
        'required' => true,
        'placeholder' => 'https://ejemplo.com',
        '_order' => 100,
    ];

    // Contact Email (optional)
    $model['fields']['contact_email'] = [
        'label' => esc_html__('Email de Contacto', 'hivepress-child'),
        'description' => esc_html__('Contact email (optional)', 'hivepress-child'),
        'type' => 'email',
        'required' => false,
        'placeholder' => 'contacto@ejemplo.com',
        '_order' => 101,
    ];

    // Phone (optional)
    $model['fields']['phone'] = [
        'label' => esc_html__('Teléfono', 'hivepress-child'),
        'description' => esc_html__('Contact phone number', 'hivepress-child'),
        'type' => 'text',
        'required' => false,
        'placeholder' => '+54 11 1234-5678',
        'max_length' => 50,
        '_order' => 102,
    ];

    // LinkedIn Founders (textarea, parse URLs line by line)
    $model['fields']['linkedin_founders'] = [
        'label' => esc_html__('LinkedIn Founders', 'hivepress-child'),
        'description' => esc_html__('LinkedIn profile URLs (one per line, max 5)', 'hivepress-child'),
        'type' => 'textarea',
        'required' => false,
        'placeholder' => "https://linkedin.com/in/founder1\nhttps://linkedin.com/in/founder2",
        'max_length' => 500,
        '_order' => 103,
    ];

    return $model;
}

// ============================================
// 3. IMAGE UPLOAD SETTINGS
// ============================================

add_filter('hivepress/v1/models/listing_attachment', 'cero1_set_image_limits');
function cero1_set_image_limits($model) {
    // Max 4 images per listing
    $model['limit'] = 4;
    return $model;
}

// Set max upload size to 5MB
add_filter('upload_size_limit', 'cero1_upload_size_limit');
function cero1_upload_size_limit($size) {
    return 5 * 1024 * 1024; // 5MB in bytes
}

// Image dimensions (1200x800)
add_action('after_setup_theme', 'cero1_image_sizes');
function cero1_image_sizes() {
    add_image_size('cero1_listing_large', 1200, 800, true);
    add_image_size('cero1_listing_thumb', 400, 267, true);
}

// ============================================
// 4. LISTING BADGE (Pending/Verified)
// ============================================

add_filter('hivepress/v1/templates/listing_view_block', 'cero1_add_listing_badge');
function cero1_add_listing_badge($template) {
    // Get listing
    $listing = hivepress()->request->get_context('listing');

    if (!$listing) {
        return $template;
    }

    // Get post status
    $status = get_post_status($listing->get_id());

    // Generate badge HTML
    $badge_class = $status === 'publish' ? 'verified' : 'pending';
    $badge_text = $status === 'publish' ? __('Verificada', 'hivepress-child') : __('Sin Verificar', 'hivepress-child');

    $badge_html = sprintf(
        '<span class="hp-listing__badge hp-listing__badge--%s">%s</span>',
        esc_attr($badge_class),
        esc_html($badge_text)
    );

    // Add badge after title
    if (isset($template['blocks']['listing_title'])) {
        $template['blocks']['listing_title']['attributes']['class'][] = 'hp-listing__title-with-badge';

        // Inject badge into title using filter
        add_filter('hivepress/v1/templates/listing_view_block/title', function($title) use ($badge_html) {
            return $title . $badge_html;
        });
    }

    return $template;
}

// ============================================
// 5. ENABLE CONTRIBUTORS TO EDIT LISTINGS
// ============================================

add_filter('map_meta_cap', 'cero1_allow_contributors_edit_listings', 10, 4);
function cero1_allow_contributors_edit_listings($caps, $cap, $user_id, $args) {
    // Allow contributors to edit their own listings
    if ($cap === 'edit_post' && !empty($args[0])) {
        $post_id = $args[0];
        $post = get_post($post_id);

        if ($post && $post->post_type === 'hp_listing' && $post->post_author == $user_id) {
            $user = get_userdata($user_id);
            if ($user && in_array('contributor', $user->roles)) {
                return ['edit_posts'];
            }
        }
    }

    return $caps;
}

// ============================================
// 6. MODERATION: LISTINGS START AS PENDING
// ============================================

add_action('transition_post_status', 'cero1_set_listing_pending', 10, 3);
function cero1_set_listing_pending($new_status, $old_status, $post) {
    // Only for hp_listing post type
    if ($post->post_type !== 'hp_listing') {
        return;
    }

    // Only for contributors (not admin)
    $author = get_userdata($post->post_author);
    if (!$author || !in_array('contributor', $author->roles)) {
        return;
    }

    // If trying to publish, set to pending instead
    if ($new_status === 'publish' && $old_status !== 'publish') {
        remove_action('transition_post_status', 'cero1_set_listing_pending', 10);

        wp_update_post([
            'ID' => $post->ID,
            'post_status' => 'pending',
        ]);

        add_action('transition_post_status', 'cero1_set_listing_pending', 10, 3);
    }
}

// ============================================
// 7. ABOUT PAGE CREATION
// ============================================

add_action('after_switch_theme', 'cero1_create_about_page');
function cero1_create_about_page() {
    // Check if "Sobre Nosotros" page exists
    $about_page = get_page_by_path('sobre-nosotros');

    if (!$about_page) {
        // Create the page
        wp_insert_post([
            'post_title' => 'Sobre Nosotros',
            'post_content' => '<h2>Texto Sobre Nosotros</h2><p>Cero1 es el marketplace de soluciones para ciudades.</p>',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'sobre-nosotros',
        ]);
    }
}

// ============================================
// 8. DISPLAY LINKEDIN FOUNDERS
// ============================================

function cero1_display_linkedin_founders($listing_id) {
    $founders_raw = get_post_meta($listing_id, 'hp_linkedin_founders', true);

    if (empty($founders_raw)) {
        return '';
    }

    // Parse URLs (line by line)
    $urls = array_filter(array_map('trim', explode("\n", $founders_raw)));
    $urls = array_slice($urls, 0, 5); // Max 5

    if (empty($urls)) {
        return '';
    }

    $html = '<section class="listing-founders">';
    $html .= '<h3>' . esc_html__('Founders', 'hivepress-child') . '</h3>';
    $html .= '<ul>';

    foreach ($urls as $index => $url) {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $html .= sprintf(
                '<li><a href="%s" target="_blank" rel="noopener">%s %d</a></li>',
                esc_url($url),
                esc_html__('LinkedIn Profile', 'hivepress-child'),
                $index + 1
            );
        }
    }

    $html .= '</ul>';
    $html .= '</section>';

    return $html;
}

// ============================================
// 9. THEME SETUP
// ============================================

add_action('after_setup_theme', 'cero1_setup_theme');
function cero1_setup_theme() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');

    // Register text domain for translations
    load_child_theme_textdomain('hivepress-child', get_stylesheet_directory() . '/languages');
}

// ============================================
// 10. CUSTOM EXCERPT LENGTH (FOR CARDS)
// ============================================

add_filter('excerpt_length', 'cero1_excerpt_length');
function cero1_excerpt_length($length) {
    return 20; // 20 words for listing cards
}

add_filter('excerpt_more', 'cero1_excerpt_more');
function cero1_excerpt_more($more) {
    return '...';
}

// ============================================
// 11. DISABLE COMMENTS (IF NOT NEEDED)
// ============================================

add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// ============================================
// 12. CUSTOM LOGO SUPPORT
// ============================================

add_action('after_setup_theme', 'cero1_custom_logo_setup');
function cero1_custom_logo_setup() {
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
}

// ============================================
// END OF FUNCTIONS
// ============================================
