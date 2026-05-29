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
mkdir -p /var/www/database
[ ! -f /var/www/database/database.sqlite ] && touch /var/www/database/database.sqlite

# Run migrations
php artisan migrate --force

# Seed database
php artisan db:seed --force || true

# Publish assets and optimize
php artisan vendor:publish --tag=filament-assets --force
php artisan optimize

# Filament optimize
php artisan filament:optimize 2>/dev/null || true

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix storage permissions
chown -R www-data:www-data /var/www/storage
chmod -R 775 /var/www/storage

# Create log dirs
mkdir -p /var/log/supervisor

# Start services via supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
