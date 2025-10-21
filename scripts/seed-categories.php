<?php
/**
 * Cero1 - Seed HivePress Categories
 * Run with: wp eval-file seed-categories.php --allow-root
 */

echo "📦 Creating HivePress categories...\n";

// Define the 5 categories with emojis
$categories = [
    [
        'name' => '🚗 Movilidad',
        'slug' => 'movilidad',
        'description' => 'Soluciones de transporte y movilidad urbana',
    ],
    [
        'name' => '🏛️ Espacio Público',
        'slug' => 'espacio-publico',
        'description' => 'Gestión y mejora de espacios públicos',
    ],
    [
        'name' => '💰 Fintech',
        'slug' => 'fintech',
        'description' => 'Tecnología financiera para ciudades',
    ],
    [
        'name' => '⚖️ LegalIA',
        'slug' => 'legalia',
        'description' => 'Inteligencia artificial aplicada a lo legal',
    ],
    [
        'name' => '📊 Datos',
        'slug' => 'datos',
        'description' => 'Análisis y visualización de datos urbanos',
    ],
];

$created_count = 0;
$existing_count = 0;

foreach ($categories as $category) {
    // Check if category already exists
    $exists = term_exists($category['slug'], 'hp_listing_category');

    if (!$exists) {
        $result = wp_insert_term(
            $category['name'],
            'hp_listing_category',
            [
                'description' => $category['description'],
                'slug' => $category['slug'],
            ]
        );

        if (!is_wp_error($result)) {
            echo "✅ Created: {$category['name']}\n";
            $created_count++;
        } else {
            echo "❌ Error creating {$category['name']}: " . $result->get_error_message() . "\n";
        }
    } else {
        echo "ℹ️  Already exists: {$category['name']}\n";
        $existing_count++;
    }
}

echo "\n📊 Summary:\n";
echo "   - Created: {$created_count}\n";
echo "   - Already existed: {$existing_count}\n";
echo "   - Total: " . ($created_count + $existing_count) . "\n";

// Mark as seeded
update_option('hivepress_categories_seeded', true);
echo "\n✅ Categories setup complete!\n";
