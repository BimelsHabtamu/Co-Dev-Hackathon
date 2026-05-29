#!/usr/bin/env bash
set -e

echo "==> Installing PHP dependencies (no dev)..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> Setting up .env..."
if [ ! -f .env ]; then
  cp .env.production .env
fi

echo "==> Generating app key (skips if already set)..."
php artisan key:generate --no-interaction --force

echo "==> Creating SQLite database file if missing..."
mkdir -p /var/data
if [ ! -f /var/data/database.sqlite ]; then
  touch /var/data/database.sqlite
  echo "    Created /var/data/database.sqlite"
fi

echo "==> Running migrations..."
php artisan migrate --force --no-interaction

echo "==> Seeding database (first deploy only)..."
php artisan db:seed --force --no-interaction || true

echo "==> Publishing Filament assets..."
php artisan vendor:publish --tag=filament-assets --force --no-interaction

echo "==> Caching config / routes / views / events..."
php artisan optimize

echo "==> Caching Filament components..."
php artisan filament:optimize

echo "==> Done. Starting server..."
