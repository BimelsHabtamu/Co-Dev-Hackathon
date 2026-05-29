#!/bin/bash
set -e

cd /var/www/html

# Copy production env
cp .env.production .env

# Inject environment variables from Render
[ -n "$APP_KEY" ] && sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
[ -n "$APP_URL" ] && sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
[ -n "$FRONTEND_URL" ] && sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=${FRONTEND_URL}|" .env
[ -n "$SANCTUM_STATEFUL_DOMAINS" ] && sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS}|" .env

# Create sqlite db if not exists
mkdir -p database
touch database/database.sqlite
chmod -R 777 storage bootstrap/cache database

# Laravel setup
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize 2>/dev/null || true

# Start nginx + php-fpm via supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
