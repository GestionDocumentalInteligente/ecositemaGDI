#!/bin/bash
set -e

echo "🚀 Cero1 - Starting WordPress setup (DEBUG MODE)..."

# DEBUG: Print all MySQL-related environment variables
echo "🔍 DEBUG: MySQL Environment Variables:"
echo "  MYSQLHOST: ${MYSQLHOST:-NOT SET}"
echo "  MYSQLPORT: ${MYSQLPORT:-NOT SET}"
echo "  MYSQLDATABASE: ${MYSQLDATABASE:-NOT SET}"
echo "  MYSQLUSER: ${MYSQLUSER:-NOT SET}"
echo "  MYSQLPASSWORD: ${MYSQLPASSWORD:+***SET***}"
echo ""

# Construct DB_HOST as wp-config.php does
if [ -n "$MYSQLHOST" ] && [ -n "$MYSQLPORT" ]; then
    DB_HOST_CONSTRUCTED="${MYSQLHOST}:${MYSQLPORT}"
else
    DB_HOST_CONSTRUCTED="db:3306"
fi

echo "🔍 DEBUG: Constructed DB_HOST: $DB_HOST_CONSTRUCTED"
echo ""

# Test MySQL connectivity with netcat/telnet
echo "⏳ Testing MySQL connectivity..."
echo "🔍 Attempting to connect to: ${MYSQLHOST:-db} on port ${MYSQLPORT:-3306}"

# Use mysqladmin to test connection
MYSQL_HOST="${MYSQLHOST:-db}"
MYSQL_PORT="${MYSQLPORT:-3306}"

MAX_TRIES=30
COUNT=0

until mysqladmin ping -h"$MYSQL_HOST" -P"$MYSQL_PORT" -u"${MYSQLUSER:-root}" -p"${MYSQLPASSWORD}" --silent 2>/dev/null; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "❌ ERROR: MySQL is unavailable after $MAX_TRIES attempts"
        echo "🔍 DEBUG: Connection details:"
        echo "   Host: $MYSQL_HOST"
        echo "   Port: $MYSQL_PORT"
        echo "   User: ${MYSQLUSER:-root}"
        echo "   Database: ${MYSQLDATABASE:-wordpress}"
        echo ""
        echo "🔍 Trying wp db check for more details..."
        wp db check --allow-root || true
        exit 1
    fi
    echo "MySQL is unavailable - sleeping (attempt $COUNT/$MAX_TRIES)"
    sleep 3
done

echo "✅ MySQL is ready!"
echo ""

# Now use wp-cli to verify
echo "🔍 Verifying with WP-CLI..."
if wp db check --allow-root; then
    echo "✅ WP-CLI database connection successful!"
else
    echo "❌ WP-CLI database connection failed!"
    echo "🔍 wp-config.php is looking for:"
    wp config get --allow-root || true
    exit 1
fi

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

# Continue with the rest of the setup...
if ! wp option get hivepress_configured --allow-root 2>/dev/null; then
    echo "🔧 Configuring HivePress..."

    if [ -f /usr/local/bin/scripts/install-plugins.sh ]; then
        bash /usr/local/bin/scripts/install-plugins.sh
    fi

    if [ -f /usr/local/bin/scripts/configure-hivepress.php ]; then
        wp eval-file /usr/local/bin/scripts/configure-hivepress.php --allow-root
    fi

    if [ -f /usr/local/bin/scripts/seed-categories.php ]; then
        wp eval-file /usr/local/bin/scripts/seed-categories.php --allow-root
    fi

    wp theme activate hivepress-child --allow-root 2>/dev/null || echo "⚠️ Child theme not found, skipping..."

    wp option update hivepress_configured true --allow-root

    echo "✅ HivePress configured!"
else
    echo "✅ HivePress already configured"
fi

echo "🔌 Ensuring plugins are activated..."
wp plugin activate hivepress --allow-root 2>/dev/null || echo "⚠️ HivePress not found"
wp plugin activate hivepress-auth0 --allow-root 2>/dev/null || echo "⚠️ Auth0 plugin not found"
wp plugin activate polylang --allow-root 2>/dev/null || echo "⚠️ Polylang not found"

echo "🔗 Setting permalink structure..."
wp rewrite structure '/%postname%/' --allow-root 2>/dev/null || echo "⚠️ Could not set permalinks"
wp rewrite flush --allow-root 2>/dev/null || echo "⚠️ Could not flush rewrites"

echo "🔒 Fixing permissions..."
chown -R www-data:www-data /var/www/html/wp-content/uploads 2>/dev/null || true

echo "✨ Setup complete! Starting Apache..."
echo "🌐 Access WordPress at: ${WP_HOME}"
echo "🔐 Admin: ${WP_ADMIN_USER} / ${WP_ADMIN_EMAIL}"
echo ""

# Execute the original Docker entrypoint
exec docker-entrypoint.sh "$@"
