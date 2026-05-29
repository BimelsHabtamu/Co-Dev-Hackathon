#!/bin/bash
set -e

# Copy production env
cp .env.production .env

# Inject environment variables from Render
[ -n "$APP_KEY" ] && sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
[ -n "$APP_URL" ] && sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
[ -n "$FRONTEND_URL" ] && sed -i "s|^FRONTEND_URL=.*|FRONTEND_URL=${FRONTEND_URL}|" .env
[ -n "$SANCTUM_STATEFUL_DOMAINS" ] && sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS}|" .env

# Generate app key if not set
php artisan key:generate --force

# Setup SQLite
mkdir -p /var/www/html/database
[ ! -f /var/www/html/database/database.sqlite ] && touch /var/www/html/database/database.sqlite

# Fix permissions
chown -R webuser:webgroup /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

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
chown -R webuser:webgroup /var/www/html/storage
chmod -R 775 /var/www/html/storage

# The serversideup image handles starting nginx+fpm automatically
# We just need to exec the default entrypoint
exec /init
