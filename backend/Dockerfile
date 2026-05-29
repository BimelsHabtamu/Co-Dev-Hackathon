FROM php:8.2-cli

# Install system dependencies + PHP extensions
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

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy application files
COPY . .

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Set storage permissions
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD ["/start.sh"]
