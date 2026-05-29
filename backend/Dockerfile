FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip nginx supervisor sqlite3 \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd \
        --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_sqlite pdo_mysql \
        mbstring bcmath zip gd intl opcache pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-dev --no-scripts --no-autoloader \
    --no-interaction --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --no-dev

RUN php artisan package:discover --ansi || true

RUN mkdir -p database storage/logs storage/framework/cache \
    storage/framework/sessions storage/framework/views \
    bootstrap/cache \
    && touch database/database.sqlite \
    && chmod -R 777 storage bootstrap/cache database

COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/bin/bash", "/start.sh"]
