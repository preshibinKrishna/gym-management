#!/bin/sh
set -eu

cd /var/www/html
mkdir -p /var/lib/gym storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data /var/lib/gym storage bootstrap/cache

if [ ! -s /var/lib/gym/app.key ]; then
    php artisan key:generate --show > /var/lib/gym/app.key
    chmod 600 /var/lib/gym/app.key
fi
export APP_KEY="$(cat /var/lib/gym/app.key)"

if [ ! -f /var/lib/gym/seeded ]; then
    touch /var/lib/gym/database.sqlite
    php artisan migrate --force
    php artisan db:seed --force
    touch /var/lib/gym/seeded
else
    php artisan migrate --force
fi

exec "$@"
