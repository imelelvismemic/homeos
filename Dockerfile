# syntax=docker/dockerfile:1

# --- Stage 1: PHP dependencies (no scripts — no app env needed at build time) ---
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
# --ignore-platform-reqs: this stage's PHP (composer image) lacks ext-intl etc.,
# they're installed in the runtime stage that actually executes the code.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# --- Stage 1b: dev dependencies (Pest, Pint, ...) for the local dev image only ---
# Kept separate so the production runtime stays --no-dev, while the dev image
# can serve a full vendor/ from a fast Docker volume (see docker-compose.override.yml).
FROM composer:2 AS vendor-dev

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --ignore-platform-reqs

COPY . .
RUN composer dump-autoload

# --- Stage 2: front-end assets (Filament custom theme, Tailwind build) ---
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# --- Stage 3: runtime image (PHP-FPM, no webserver, no TLS — see CLAUDE.md §3a) ---
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
        icu-libs \
        libzip \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl zip opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN addgroup -g 1000 www \
    && adduser -G www -g www -s /bin/sh -D www \
    && chown -R www:www /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint.sh

USER www

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# --- Stage 4: local dev only (adds Xdebug) — selected via `build.target: dev` ---
FROM runtime AS dev

USER root

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY docker/opcache-dev.ini /usr/local/etc/php/conf.d/zz-opcache-dev.ini

# Full vendor/ (incl. dev deps: Pest, Pint) so tools run inside the container and
# so the anonymous vendor volume in docker-compose.override.yml is seeded from a
# fast image layer instead of the slow Windows bind mount.
COPY --from=vendor-dev /app/vendor ./vendor

# Local dev bind-mounts the host source over /var/www/html (docker-compose.override.yml),
# which arrives owned by root — run the FPM pool as root too so it can write to
# storage/bootstrap/cache regardless of host-side ownership. Never done in `runtime`.
RUN sed -i 's/^user = .*/user = root/; s/^group = .*/group = root/' /usr/local/etc/php-fpm.d/www.conf

CMD ["php-fpm", "-R"]
