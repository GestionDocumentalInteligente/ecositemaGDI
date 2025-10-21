#!/bin/bash
set -e

echo "🚀 Cero1 - Starting WordPress setup..."
echo ""

# STEP 1: Trigger WordPress file copy by calling original entrypoint in setup mode
# The wordpress:6.4-apache image copies files from /usr/src/wordpress/ to /var/www/html/
# We need to trigger this, but without starting Apache yet
echo "📥 Triggering WordPress file initialization..."

# Check if WordPress files already exist (from previous container run)
if [ ! -f /var/www/html/wp-settings.php ]; then
    echo "WordPress files not found, copying from /usr/src/wordpress/..."

    # Use the WordPress entrypoint's built-in file copying logic
    # We'll call it with a dummy command that will copy files then exit
    /usr/local/bin/docker-entrypoint.sh wp --version --allow-root 2>/dev/null || true

    # Wait for WordPress files to appear
    MAX_WAIT=30
    COUNT=0
    until [ -f /var/www/html/wp-settings.php ] || [ $COUNT -ge $MAX_WAIT ]; do
        COUNT=$((COUNT + 1))
        echo "Waiting for WordPress files... ($COUNT/$MAX_WAIT)"
        sleep 1
    done
fi

if [ -f /var/www/html/wp-settings.php ]; then
    echo "✅ WordPress core files are present!"
else
    echo "❌ ERROR: WordPress files still missing after initialization attempt"
    echo "Listing /var/www/html contents:"
    ls -la /var/www/html/ || true
    exit 1
fi
echo ""

# STEP 2: Copy our custom files from staging directory to WordPress directory
echo "📋 Copying custom files from staging directory..."

# Copy wp-config.php
if [ -f /opt/cero1/wp-config.php ]; then
    echo "Copying custom wp-config.php..."
    cp /opt/cero1/wp-config.php /var/www/html/wp-config.php
fi

# Copy custom theme
if [ -d /opt/cero1/wp-content/themes/hivepress-child ]; then
    echo "Copying hivepress-child theme..."
    mkdir -p /var/www/html/wp-content/themes
    cp -r /opt/cero1/wp-content/themes/hivepress-child /var/www/html/wp-content/themes/
fi

# Copy custom plugins
if [ -d /opt/cero1/wp-content/plugins/hivepress-auth0 ]; then
    echo "Copying hivepress-auth0 plugin..."
    mkdir -p /var/www/html/wp-content/plugins
    cp -r /opt/cero1/wp-content/plugins/hivepress-auth0 /var/www/html/wp-content/plugins/
fi

# Copy must-use plugins
if [ -d /opt/cero1/wp-content/mu-plugins ]; then
    echo "Copying mu-plugins..."
    mkdir -p /var/www/html/wp-content/mu-plugins
    cp -r /opt/cero1/wp-content/mu-plugins/* /var/www/html/wp-content/mu-plugins/
fi

echo "✅ Custom files copied successfully!"
echo ""

# STEP 3: Display environment configuration
echo "========================================="
echo "Environment Configuration"
echo "========================================="
echo "MYSQLHOST: ${MYSQLHOST:-NOT SET}"
echo "MYSQLDATABASE: ${MYSQLDATABASE:-NOT SET}"
echo "MYSQLUSER: ${MYSQLUSER:-NOT SET}"
echo "MYSQLPASSWORD: ${MYSQLPASSWORD:+***SET***}"
echo "MYSQLPORT: ${MYSQLPORT:-NOT SET}"
echo "WP_HOME: ${WP_HOME:-NOT SET}"
echo "WP_ADMIN_EMAIL: ${WP_ADMIN_EMAIL:-NOT SET}"
echo "========================================="
echo ""

# STEP 4: Wait for MySQL to be ready
DB_HOST="${MYSQLHOST:-mysql.railway.internal}"
DB_PORT="${MYSQLPORT:-3306}"

echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
MAX_TRIES=60
COUNT=0

until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${MYSQLUSER:-root}" -p"${MYSQLPASSWORD}" --silent 2>&1; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "❌ ERROR: MySQL is unavailable after $MAX_TRIES attempts"
        echo "Connection details: ${DB_HOST}:${DB_PORT} / user: ${MYSQLUSER:-root}"
        exit 1
    fi
    echo "MySQL is unavailable - sleeping (attempt $COUNT/$MAX_TRIES)"
    sleep 3
done

echo "✅ MySQL is ready!"
echo ""

# STEP 5: Install WordPress if not already installed
if ! wp core is-installed --allow-root 2>/dev/null; then
    echo "📦 Installing WordPress..."

    wp core install \
        --url="${WP_HOME}" \
        --title="Cero1 - Marketplace de Soluciones para Ciudades" \
        --admin_user="${WP_ADMIN_USER:-admin}" \
        --admin_password="${WP_ADMIN_PASSWORD}" \
        --admin_email="${WP_ADMIN_EMAIL}" \
        --skip-email \
        --allow-root

    echo "✅ WordPress installed successfully!"
else
    echo "✅ WordPress already installed"
fi
echo ""

# STEP 6: Configure HivePress (one-time setup)
if ! wp option get hivepress_configured --allow-root 2>/dev/null; then
    echo "🔧 Configuring HivePress..."

    # Run plugin installation script
    if [ -f /usr/local/bin/scripts/install-plugins.sh ]; then
        bash /usr/local/bin/scripts/install-plugins.sh
    fi

    # Configure HivePress custom fields
    if [ -f /usr/local/bin/scripts/configure-hivepress.php ]; then
        wp eval-file /usr/local/bin/scripts/configure-hivepress.php --allow-root
    fi

    # Seed categories
    if [ -f /usr/local/bin/scripts/seed-categories.php ]; then
        wp eval-file /usr/local/bin/scripts/seed-categories.php --allow-root
    fi

    # Activate child theme
    wp theme activate hivepress-child --allow-root 2>/dev/null || echo "⚠️ Child theme not found, skipping..."

    # Mark as configured
    wp option update hivepress_configured true --allow-root

    echo "✅ HivePress configured!"
else
    echo "✅ HivePress already configured"
fi
echo ""

# STEP 7: Ensure plugins are activated
echo "🔌 Ensuring plugins are activated..."
wp plugin activate hivepress --allow-root 2>/dev/null || echo "⚠️ HivePress not found"
wp plugin activate hivepress-auth0 --allow-root 2>/dev/null || echo "⚠️ Auth0 plugin not found"
wp plugin activate polylang --allow-root 2>/dev/null || echo "⚠️ Polylang not found"
echo ""

# STEP 8: Set permalink structure
echo "🔗 Setting permalink structure..."
wp rewrite structure '/%postname%/' --allow-root 2>/dev/null || echo "⚠️ Could not set permalinks"
wp rewrite flush --allow-root 2>/dev/null || echo "⚠️ Could not flush rewrites"
echo ""

# STEP 9: Fix permissions
echo "🔒 Fixing permissions..."
chown -R www-data:www-data /var/www/html
find /var/www/html -type d -exec chmod 755 {} \; 2>/dev/null || true
find /var/www/html -type f -exec chmod 644 {} \; 2>/dev/null || true
echo ""

echo "✨ Setup complete! Starting Apache..."
echo "🌐 Access WordPress at: ${WP_HOME}"
echo "🔐 Admin: ${WP_ADMIN_USER:-admin} / ${WP_ADMIN_EMAIL}"
echo ""

# STEP 10: Execute the original WordPress entrypoint with Apache
# Use exec to replace this process with Apache, ensuring proper signal handling
exec /usr/local/bin/docker-entrypoint.sh "$@"
