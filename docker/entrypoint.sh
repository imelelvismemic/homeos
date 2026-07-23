#!/bin/sh
set -e

until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    echo "Čekam bazu (${DB_HOST}:${DB_PORT})..."
    sleep 2
done

# Samo app servis (RUN_MIGRATIONS=true) generiše ključ i migrira; queue-worker i
# scheduler čekaju da app postane healthy (docker-compose.yml) pa .env već ima ključ.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    # Provjeravamo .env FAJL, ne $APP_KEY env varijablu: `env_file: .env` je pri
    # startu učitao APP_KEY="" u okruženje, pa bi gate na env varijabli uvijek bio
    # istinit i `key:generate` bi se vrtio na svaki restart. Uz prazan config ključ
    # (isti shadow) Laravelov key:generate poklapa samo `APP_KEY=` prefiks i doda
    # novi ključ ISPRED starog — višestruko pokretanje spoji više ključeva u jedan
    # neispravan. Zato generišemo najviše jednom, kad .env stvarno nema base64 ključ.
    if ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
        echo "APP_KEY nije postavljen, generišem novi..."
        php artisan key:generate --force
    fi

    php artisan migrate --force
fi

# `env_file: .env` učita APP_KEY="" u okruženje kontejnera, a Laravel Dotenv NE
# prepisuje već postavljenu env varijablu — pa bi prazna vrijednost zasjenila ključ
# iz .env fajla (500 "No application encryption key"). Ako je prazan, pročitaj ga iz
# .env da ga php-fpm/artisan djeca naslijede. U produkciji je APP_KEY pravi env var
# (ne prazan), pa je ovaj blok no-op.
if [ -z "$APP_KEY" ] && [ -f .env ]; then
    export APP_KEY="$(grep -E '^APP_KEY=' .env | head -1 | cut -d '=' -f 2-)"
fi

exec "$@"
