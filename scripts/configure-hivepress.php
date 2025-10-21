<?php
/**
 * Cero1 - Configure HivePress Custom Fields
 * Run with: wp eval-file configure-hivepress.php --allow-root
 */

echo "🔧 Configuring HivePress custom fields...\n";

// This code will be moved to the child theme's functions.php
// For now, we're setting an option to trigger the configuration

// Register custom fields via filter (this will be in child theme)
add_filter('hivepress/v1/models/listing', function($model) {

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

    // LinkedIn Founders (repeater - up to 5)
    // Note: HivePress free version may have limited repeater support
    // We'll use a textarea for now and parse URLs
    $model['fields']['linkedin_founders'] = [
        'label' => esc_html__('LinkedIn Founders', 'hivepress-child'),
        'description' => esc_html__('LinkedIn profile URLs (one per line, max 5)', 'hivepress-child'),
        'type' => 'textarea',
        'required' => false,
        'placeholder' => 'https://linkedin.com/in/founder1
https://linkedin.com/in/founder2',
        'max_length' => 500,
        '_order' => 103,
    ];

    return $model;
});

// Set HivePress settings
$hivepress_settings = [
    // Listing settings
    'listing_allow_attachment' => true,
    'listing_attachment_limit' => 4, // Max 4 images
    'listing_image_width' => 1200,
    'listing_image_height' => 800,

    // Moderation
    'listing_enable_moderation' => true, // Listings start as pending

    // Categories
    'listing_enable_categories' => true,
];

foreach ($hivepress_settings as $key => $value) {
    update_option('hp_' . $key, $value);
}

echo "✅ HivePress custom fields configured!\n";
echo "✅ Settings: max 4 images (1200x800px), moderation enabled\n";

// Mark as configured
update_option('hivepress_fields_configured', true);
