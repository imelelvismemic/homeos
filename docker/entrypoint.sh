#!/bin/sh
set -e

until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    echo "Čekam bazu (${DB_HOST}:${DB_PORT})..."
    sleep 2
done

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
