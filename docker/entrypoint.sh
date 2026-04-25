#!/bin/sh
set -e

echo "==> [entrypoint] Laravel 12 | PHP 8.4 | Production"

# Pastikan direktori storage & cache ada dan writable
mkdir -p /var/www/html/storage/framework/{cache,sessions,views}
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Tunggu PostgreSQL siap
echo "==> [entrypoint] Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
until php -r "
    try {
        new PDO(
            'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );
        echo 'OK' . PHP_EOL;
    } catch (Exception \$e) {
        echo 'waiting... ' . \$e->getMessage() . PHP_EOL;
        exit(1);
    }
" 2>/dev/null; do
    sleep 2
done
echo "==> [entrypoint] PostgreSQL ready."

cd /var/www/html

# Migrasi database
echo "==> [entrypoint] Running migrations..."
php artisan migrate --force

# Cache production
echo "==> [entrypoint] Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Storage link
php artisan storage:link --quiet || true

echo "==> [entrypoint] Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
