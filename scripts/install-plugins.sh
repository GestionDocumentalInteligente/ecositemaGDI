#!/bin/bash
set -e

echo "📦 Installing WordPress plugins..."

# Install HivePress
if ! wp plugin is-installed hivepress --allow-root 2>/dev/null; then
    echo "Installing HivePress..."
    wp plugin install hivepress --activate --allow-root
    echo "✅ HivePress installed"
else
    echo "✅ HivePress already installed"
    wp plugin activate hivepress --allow-root 2>/dev/null || true
fi

# Install Polylang (for i18n)
if ! wp plugin is-installed polylang --allow-root 2>/dev/null; then
    echo "Installing Polylang..."
    wp plugin install polylang --activate --allow-root
    echo "✅ Polylang installed"
else
    echo "✅ Polylang already installed"
    wp plugin activate polylang --allow-root 2>/dev/null || true
fi

# Install WP Super Cache (for performance)
if ! wp plugin is-installed wp-super-cache --allow-root 2>/dev/null; then
    echo "Installing WP Super Cache..."
    wp plugin install wp-super-cache --allow-root
    echo "✅ WP Super Cache installed (not activated yet)"
else
    echo "✅ WP Super Cache already installed"
fi

# Configure Polylang languages
if ! wp option get polylang_configured --allow-root 2>/dev/null; then
    echo "Configuring Polylang languages..."

    # Create ES language
    wp pll lang create es "Español" es_ES --allow-root 2>/dev/null || echo "ES already exists"

    # Create EN language
    wp pll lang create en "English" en_US --allow-root 2>/dev/null || echo "EN already exists"

    # Set default language to Spanish
    wp pll option update default_lang es --allow-root 2>/dev/null || true

    wp option update polylang_configured true --allow-root
    echo "✅ Polylang configured (ES, EN)"
fi

echo "✅ All plugins installed and configured!"
