#!/bin/sh
set -eu

# Composer dependencies and Vite assets are intentionally installed during
# `docker compose up -d --build` in the Dockerfile. Installing them here would
# make every replica startup slow, non-reproducible, and dependent on internet.
if [ ! -f vendor/autoload.php ]; then
    echo >&2 "ERROR: Composer dependencies are missing. Rebuild with: docker compose up -d --build"
    exit 1
fi

if [ ! -f public/build/manifest.json ]; then
    echo >&2 "ERROR: Vite assets are missing. Rebuild with: docker compose up -d --build"
    exit 1
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug=rwX storage bootstrap/cache

# Recreate the public storage symlink when a fresh named volume is attached.
if [ ! -L public/storage ]; then
    rm -rf public/storage
    php artisan storage:link --force --no-interaction
fi

exec "$@"
