# Cero1 - Marketplace de Soluciones para Ciudades
# WordPress + HivePress + Auth0

FROM wordpress:6.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    less \
    mariadb-client \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp \
    && wp --info --allow-root

# Install PHP extensions (if needed)
RUN docker-php-ext-install mysqli exif

# Enable Apache modules
RUN a2enmod rewrite expires headers

# Set working directory
WORKDIR /var/www/html

# Create staging directory for our custom files
# This prevents interference with WordPress's runtime initialization
RUN mkdir -p /opt/cero1/wp-content/themes \
    && mkdir -p /opt/cero1/wp-content/plugins \
    && mkdir -p /opt/cero1/wp-content/mu-plugins

# Copy custom wp-config.php to staging
COPY config/wp-config.php /opt/cero1/wp-config.php

# Copy scripts
COPY scripts/ /usr/local/bin/scripts/
RUN chmod +x /usr/local/bin/scripts/*.sh

# Copy custom themes to staging
COPY wp-content/themes/hivepress-child /opt/cero1/wp-content/themes/hivepress-child

# Copy custom plugins to staging
COPY wp-content/plugins/hivepress-auth0 /opt/cero1/wp-content/plugins/hivepress-auth0

# Copy must-use plugins to staging
COPY wp-content/mu-plugins /opt/cero1/wp-content/mu-plugins

# Set permissions on staging directory
RUN chown -R www-data:www-data /opt/cero1 \
    && find /opt/cero1 -type d -exec chmod 755 {} \; \
    && find /opt/cero1 -type f -exec chmod 644 {} \;

# Copy our custom entrypoint script
COPY scripts/entrypoint.sh /usr/local/bin/cero1-entrypoint.sh
RUN chmod +x /usr/local/bin/cero1-entrypoint.sh

# Expose port
EXPOSE 80

# Use our custom entrypoint
ENTRYPOINT ["/usr/local/bin/cero1-entrypoint.sh"]
CMD ["apache2-foreground"]
