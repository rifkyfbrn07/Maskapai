#!/bin/sh
set -eu

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug=rwX storage bootstrap/cache

# Recreate the public storage symlink when a fresh named volume is attached.
if [ ! -L public/storage ]; then
    rm -rf public/storage
    php artisan storage:link --force --no-interaction
fi

exec "$@"
