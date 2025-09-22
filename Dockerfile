# Extend the existing node-php Dockerfile with Composer
FROM serversideup/php:8.4-fpm-nginx

# Switch to root user for installation
USER root

# Install Node.js 24
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Pre-create Laravel directories with proper ownership and permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    bootstrap/cache \
    && chown -R www-data:www-data . \
    && chmod -R 775 storage bootstrap/cache

# Switch back to www-data user
USER www-data

# Verify installations
RUN php --version && node --version && npm --version && composer --version