#!/bin/bash
set -e

cd /var/www/html

touch database/database.sqlite
chmod 777 database/database.sqlite

php artisan key:generate --force
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
