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

# Copy custom wp-config.php
COPY config/wp-config.php /var/www/html/wp-config.php

# Copy scripts
COPY scripts/ /usr/local/bin/scripts/
RUN chmod +x /usr/local/bin/scripts/*.sh

# Copy custom themes
COPY wp-content/themes/hivepress-child /var/www/html/wp-content/themes/hivepress-child

# Copy custom plugins
COPY wp-content/plugins/hivepress-auth0 /var/www/html/wp-content/plugins/hivepress-auth0

# Copy must-use plugins
COPY wp-content/mu-plugins /var/www/html/wp-content/mu-plugins

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Copy custom entrypoint
COPY scripts/entrypoint.sh /usr/local/bin/custom-entrypoint.sh
RUN chmod +x /usr/local/bin/custom-entrypoint.sh

# Expose port
EXPOSE 80

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/custom-entrypoint.sh"]
CMD ["apache2-foreground"]
