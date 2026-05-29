#!/bin/bash
set -e

echo "==> Setting up environment..."
cp .env.production .env

# Inject APP_KEY from environment variable (set on Render dashboard)
if [ -n "$APP_KEY" ]; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
fi

# Inject other env vars if provided
[ -n "$APP_URL" ]    && sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
[ -n "$FRONTEND_URL" ] && sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=${FRONTEND_URL}|" .env
[ -n "$SANCTUM_STATEFUL_DOMAINS" ] && sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS}|" .env

echo "==> Setting up SQLite database..."
mkdir -p /var/data
if [ ! -f /var/data/database.sqlite ]; then
    touch /var/data/database.sqlite
    echo "    Created fresh database"
fi

# Fix permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding database..."
php artisan db:seed --force || true

echo "==> Publishing Filament assets..."
php artisan vendor:publish --tag=filament-assets --force

echo "==> Optimizing..."
php artisan optimize
php artisan filament:optimize

echo "==> Starting Laravel server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
