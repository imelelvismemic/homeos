#!/bin/sh
set -e

until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    echo "Čekam bazu (${DB_HOST}:${DB_PORT})..."
    sleep 2
done

# Samo app servis (RUN_MIGRATIONS=true) generiše ključ i migrira; queue-worker i
# scheduler čekaju da app postane healthy (docker-compose.yml) pa .env već ima ključ.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    if [ -z "$APP_KEY" ]; then
        echo "APP_KEY nije postavljen, generišem novi..."
        php artisan key:generate --force
    fi

    php artisan migrate --force
fi

# `env_file: .env` učita APP_KEY="" u okruženje kontejnera pri startu, a Laravel
# Dotenv NE prepisuje već postavljenu env varijablu — pa bi prazna vrijednost
# zasjenila ključ iz .env fajla (500 "No application encryption key"). Ako je
# prazan, pročitaj ga iz .env da ga php-fpm/artisan djeca naslijede.
# Produkcija postavlja pravi APP_KEY kao env var, pa je ovaj blok no-op tamo.
if [ -z "$APP_KEY" ] && [ -f .env ]; then
    export APP_KEY="$(grep -E '^APP_KEY=' .env | head -1 | cut -d '=' -f 2-)"
fi

exec "$@"
