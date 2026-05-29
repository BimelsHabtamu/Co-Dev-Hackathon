#!/bin/bash
set -e

# Copy production env
cp .env.production .env

# Inject APP_KEY from Render environment variable
[ -n "$APP_KEY" ] && sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
[ -n "$APP_URL" ] && sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
[ -n "$FRONTEND_URL" ] && sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=${FRONTEND_URL}|" .env
[ -n "$SANCTUM_STATEFUL_DOMAINS" ] && sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS}|" .env

# Generate app key if not set
php artisan key:generate --force

# Setup SQLite
mkdir -p /var/data
[ ! -f /var/data/database.sqlite ] && touch /var/data/database.sqlite

# Fix permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/data
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force || true

# Publish assets and optimize
php artisan vendor:publish --tag=filament-assets --force
php artisan optimize
php artisan filament:optimize

# Create log dirs
mkdir -p /var/log/supervisor

# Start nginx + php-fpm
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
