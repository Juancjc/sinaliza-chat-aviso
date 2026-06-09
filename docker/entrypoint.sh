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
        $driver = getenv("DB_CONNECTION") ?: "pgsql";
        $host = getenv("DB_HOST") ?: "database";
        $port = getenv("DB_PORT") ?: "5432";
        $database = getenv("DB_DATABASE") ?: "chat_aviso";
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

wait_for_redis() {
    attempts=0

    until php -r '
        $redis = new Redis();
        $redis->connect(getenv("REDIS_HOST") ?: "redis", (int) (getenv("REDIS_PORT") ?: 6379), 2);
        exit($redis->ping() ? 0 : 1);
    ' > /dev/null 2>&1
    do
        attempts=$((attempts + 1))

        if [ "$attempts" -ge 60 ]; then
            echo "Redis indisponível após 60 tentativas."
            exit 1
        fi

        sleep 2
    done
}

wait_for_database
wait_for_redis

chown -R www-data:www-data storage bootstrap/cache

php artisan optimize:clear
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
