#!/bin/bash
set -e

echo "==> Setting up environment..."
cp .env.production .env

# Inject environment variables from Render into .env
[ -n "$APP_KEY" ]    && sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
[ -n "$APP_URL" ]    && sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
[ -n "$FRONTEND_URL" ] && sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=${FRONTEND_URL}|" .env
[ -n "$SANCTUM_STATEFUL_DOMAINS" ] && sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS}|" .env

echo "==> Setting up SQLite database..."
mkdir -p /var/data
if [ ! -f /var/data/database.sqlite ]; then
    touch /var/data/database.sqlite
    echo "    Created fresh SQLite database"
fi

# Fix permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/data
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding database..."
php artisan db:seed --force || true

echo "==> Publishing Filament assets..."
php artisan vendor:publish --tag=filament-assets --force

echo "==> Optimizing Laravel..."
php artisan optimize
php artisan filament:optimize

echo "==> Creating nginx log directory..."
mkdir -p /var/log/supervisor

echo "==> Starting nginx + php-fpm via supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
