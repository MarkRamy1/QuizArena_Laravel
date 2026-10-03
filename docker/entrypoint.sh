#!/bin/sh
set -e

echo "==> [QuizArena ULTRA] Container Starting..."

# Configure Nginx port dynamically based on $PORT env var (Render, Railway, Fly.io, Cloud Run)
PORT="${PORT:-80}"
echo "==> Configuring Nginx to listen on port: ${PORT}"
sed -i "s/listen 80/listen ${PORT}/g" /etc/nginx/http.d/default.conf 2>/dev/null || true
sed -i "s/listen \[::\]:80/listen [::]:${PORT}/g" /etc/nginx/http.d/default.conf 2>/dev/null || true

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure symlink for public storage
php artisan storage:link --force 2>/dev/null || true

# Run database migrations if enabled (default: true)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "==> Running database migrations..."
    php artisan migrate --force || echo "Migration encountered an issue or database is not yet ready."
fi

# Run seeders if RUN_DB_SEED=true
if [ "${RUN_DB_SEED:-false}" = "true" ]; then
    echo "==> Running database seeders..."
    php artisan db:seed --force || echo "Seeder skipped or failed."
fi

# Cache configurations for maximum production performance
echo "==> Optimizing Laravel cache..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "==> [QuizArena ULTRA] Ready! Starting Web Server & PHP-FPM..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
