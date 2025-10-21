#!/bin/bash
set -e

echo "🚀 Cero1 - Starting WordPress setup..."
echo ""
echo "========================================="
echo "DEBUG: Environment Variables"
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

# Construct DB host
DB_HOST="${MYSQLHOST:-mysql.railway.internal}"
DB_PORT="${MYSQLPORT:-3306}"
DB_FULL_HOST="${DB_HOST}:${DB_PORT}"

echo "🔍 Constructed DB connection: ${DB_FULL_HOST}"
echo "🔍 Database name: ${MYSQLDATABASE:-railway}"
echo "🔍 Database user: ${MYSQLUSER:-root}"
echo ""

# Test raw MySQL connectivity with mysqladmin
echo "⏳ Testing MySQL connectivity with mysqladmin..."
MAX_TRIES=60
COUNT=0

until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${MYSQLUSER:-root}" -p"${MYSQLPASSWORD}" --silent 2>&1; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "❌ ERROR: MySQL is unavailable after $MAX_TRIES attempts"
        echo ""
        echo "🔍 Final connection details:"
        echo "   Host: ${DB_HOST}"
        echo "   Port: ${DB_PORT}"
        echo "   User: ${MYSQLUSER:-root}"
        echo "   Database: ${MYSQLDATABASE:-railway}"
        echo ""
        echo "🔍 Attempting DNS resolution..."
        nslookup ${DB_HOST} || echo "DNS lookup failed"
        echo ""
        echo "🔍 Attempting ping..."
        ping -c 3 ${DB_HOST} || echo "Ping failed"
        echo ""
        echo "🔍 Attempting telnet test..."
        timeout 5 bash -c "echo -n > /dev/tcp/${DB_HOST}/${DB_PORT}" 2>&1 && echo "Port is open!" || echo "Port is closed or unreachable"
        exit 1
    fi
    echo "MySQL is unavailable - sleeping (attempt $COUNT/$MAX_TRIES)"
    sleep 3
done
echo "✅ MySQL is ready!"
echo ""

# Now test with wp-cli
echo "🔍 Testing with WP-CLI..."
if wp db check --allow-root 2>&1; then
    echo "✅ WP-CLI can connect to database!"
else
    echo "❌ WP-CLI cannot connect to database"
    echo "🔍 wp-config.php database constants:"
    wp config get DB_HOST --allow-root || echo "DB_HOST not set"
    wp config get DB_NAME --allow-root || echo "DB_NAME not set"
    wp config get DB_USER --allow-root || echo "DB_USER not set"
    exit 1
fi
echo ""

# Check if WordPress is already installed
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

# Check if HivePress setup is done
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

# Ensure plugins are activated
echo "🔌 Ensuring plugins are activated..."
wp plugin activate hivepress --allow-root 2>/dev/null || echo "⚠️ HivePress not found"
wp plugin activate hivepress-auth0 --allow-root 2>/dev/null || echo "⚠️ Auth0 plugin not found"
wp plugin activate polylang --allow-root 2>/dev/null || echo "⚠️ Polylang not found"

# Set permalink structure
echo "🔗 Setting permalink structure..."
wp rewrite structure '/%postname%/' --allow-root 2>/dev/null || echo "⚠️ Could not set permalinks"
wp rewrite flush --allow-root 2>/dev/null || echo "⚠️ Could not flush rewrites"

# Fix permissions
echo "🔒 Fixing permissions..."
chown -R www-data:www-data /var/www/html/wp-content/uploads 2>/dev/null || true

echo "✨ Setup complete! Starting Apache..."
echo "🌐 Access WordPress at: ${WP_HOME}"
echo "🔐 Admin: ${WP_ADMIN_USER} / ${WP_ADMIN_EMAIL}"
echo ""

# Execute the original Docker entrypoint
exec docker-entrypoint.sh "$@"
