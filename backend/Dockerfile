FROM php:8.2-cli

# Install system dependencies + all required PHP extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libzip-dev libpng-dev libonig-dev libxml2-dev \
    libicu-dev libfreetype6-dev libjpeg62-turbo-dev \
    sqlite3 libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_sqlite zip mbstring exif pcntl bcmath gd intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy OPcache config
COPY php.ini /usr/local/etc/php/conf.d/opcache.ini

# Set PHP memory limit high for composer
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies with verbose output to see errors
RUN php -d memory_limit=512M /usr/bin/composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs \
    --verbose

# Copy all application files
COPY . .

# Set storage permissions
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Make startup script executable
RUN chmod +x /var/www/start.sh

EXPOSE 8000

CMD ["/var/www/start.sh"]
