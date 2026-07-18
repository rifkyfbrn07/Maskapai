# syntax=docker/dockerfile:1.7

# Build Vite assets separately so Node.js is not present in the runtime image.
FROM node:22-bookworm-slim AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

# Install PHP dependencies separately for a reproducible production build.
FROM composer:2 AS dependencies
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

FROM php:8.3-apache-bookworm AS application
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl libicu-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl opcache pdo_mysql zip \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY --from=dependencies /app/vendor ./vendor
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Generate Laravel's package manifest after the full application is available.
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug=rwx storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/laravel-entrypoint
RUN chmod +x /usr/local/bin/laravel-entrypoint

EXPOSE 80
HEALTHCHECK --interval=10s --timeout=3s --start-period=30s --retries=5 \
    CMD curl --fail --silent http://127.0.0.1/up || exit 1

ENTRYPOINT ["laravel-entrypoint"]
CMD ["apache2-foreground"]
