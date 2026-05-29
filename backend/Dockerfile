FROM serversideup/php:8.2-fpm-nginx

# Switch to root to install extras
USER root

# Install only the extra PHP extensions we need
RUN install-php-extensions intl gd zip bcmath opcache pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies - no scripts yet (artisan not copied yet)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist

# Copy all app files
COPY . .

# Run autoloader + package discovery now that artisan exists
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Permissions
RUN chown -R webuser:webgroup /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 8080
