#!/usr/bin/env sh

set -eu

cd /var/www/html

mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    APP_KEY_FILE="storage/app/.app-key"

    if [ ! -s "$APP_KEY_FILE" ]; then
        php artisan key:generate --show > "$APP_KEY_FILE"
    fi

    export APP_KEY="$(cat "$APP_KEY_FILE")"
fi

if [ -z "${REVERB_APP_SECRET:-}" ]; then
    REVERB_SECRET_FILE="storage/app/.reverb-secret"

    if [ ! -s "$REVERB_SECRET_FILE" ]; then
        php -r "echo bin2hex(random_bytes(32));" > "$REVERB_SECRET_FILE"
    fi

    export REVERB_APP_SECRET="$(cat "$REVERB_SECRET_FILE")"
fi

wait_for_database() {
    attempts=0

    until php -r '
        $driver = getenv("DB_CONNECTION") ?: "sqlite";
        $database = getenv("DB_DATABASE") ?: "/var/www/html/storage/app/database.sqlite";

        if ($driver === "sqlite") {
            touch($database);
            new PDO("sqlite:$database");
            exit(0);
        }

        $host = getenv("DB_HOST") ?: "postgres";
        $port = getenv("DB_PORT") ?: "5432";
        $username = getenv("DB_USERNAME") ?: "chat_aviso";
        $password = getenv("DB_PASSWORD") ?: "";
        new PDO("$driver:host=$host;port=$port;dbname=$database", $username, $password);
    ' > /dev/null 2>&1
    do
        attempts=$((attempts + 1))

        if [ "$attempts" -ge 60 ]; then
            echo "Banco de dados indisponível após 60 tentativas."
            exit 1
        fi

        sleep 2
    done
}

wait_for_database

chown -R www-data:www-data storage bootstrap/cache

php artisan optimize:clear
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
