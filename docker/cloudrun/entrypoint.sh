#!/bin/bash
set -e

echo "==> Starting NeoERP on Google Cloud Run..."

# 1. Substitute dynamic PORT in nginx configuration (Cloud Run assigns dynamic PORT, default 8080)
PORT="${PORT:-8080}"
echo "==> Configuring Nginx to listen on port ${PORT}..."
sed -i "s/8080/${PORT}/g" /etc/nginx/conf.d/default.conf

# 2. Ensure database directory & sqlite file exist
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "==> Creating SQLite database file..."
    touch /var/www/html/database/database.sqlite
fi

# 3. Ensure production .env exists if missing
if [ ! -f /var/www/html/.env ]; then
    echo "==> Initializing production .env file..."
    cat << 'EOF' > /var/www/html/.env
APP_NAME=NeoERP
APP_ENV=production
APP_KEY=base64:WHJyeBfOSdjJ8NeEzeIg3LaLTAQbDcKvTbyRbuxwsgM=
APP_DEBUG=false
APP_URL=https://localhost

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=false

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=file

INITIAL_ADMIN_EMAIL=admin@neoerp.local
INITIAL_ADMIN_PASSWORD=NeoErp!Admin2026#Secure
EOF
fi

# 4. Generate app key if needed
if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "==> Generating Laravel APP_KEY..."
    php artisan key:generate --force
fi

# 5. Run database migrations & showcase seeding
echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Seeding showcase accounts and commercial demo data..."
php artisan db:seed --force
php artisan auth:ensure-admin || true

# 6. Storage symlink and caching
echo "==> Linking storage and optimizing application caches..."
php artisan storage:link --force || true
php artisan optimize:clear
php artisan optimize

# 7. Ensure correct file permissions
echo "==> Setting directory permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 664 /var/www/html/database/database.sqlite || true

# 8. Start PHP-FPM daemon
echo "==> Launching PHP-FPM..."
php-fpm -D

# 9. Start Nginx in foreground
echo "==> NeoERP is ready! Launching Nginx web server on port ${PORT}..."
exec nginx -g "daemon off;"
