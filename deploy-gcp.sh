#!/bin/bash
# ==============================================================================
# NeoERP — Automated Deployment Script for Google Cloud Platform (Compute Engine)
# ==============================================================================
set -e

echo "================================================================="
echo "   NeoERP Cloud Deployment — Initializing on Google Cloud"
echo "================================================================="

# 1. Check & Install Docker & Docker Compose if missing
if ! command -v docker &> /dev/null; then
    echo "==> Docker not found. Installing Docker Engine..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm -f get-docker.sh
    if [ -n "$SUDO_USER" ]; then
        usermod -aG docker "$SUDO_USER"
    elif [ -n "$USER" ]; then
        usermod -aG docker "$USER" || true
    fi
    echo "==> Docker installed successfully."
fi

# Ensure docker service is running
if command -v systemctl &> /dev/null; then
    sudo systemctl enable --now docker || true
fi

# Determine docker compose command (compose plugin or docker-compose)
if docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
elif command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
else
    echo "==> Installing Docker Compose plugin..."
    sudo apt-get update && sudo apt-get install -y docker-compose-plugin
    DOCKER_COMPOSE="docker compose"
fi

# 2. Configure .env file
if [ ! -f .env ]; then
    echo "==> Creating production .env file..."
    cat << 'EOF' > .env
APP_NAME="NEO ERP"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=neoerp
DB_USERNAME=neoerp_user
DB_PASSWORD=neoerp_secret

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MEMCACHED_HOST=127.0.0.1

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@neoerp.local"
MAIL_FROM_NAME="${APP_NAME}"

INITIAL_ADMIN_EMAIL=admin@neoerp.local
INITIAL_ADMIN_PASSWORD=NeoErp!Admin2026#Secure

WEB_PORT=80
EOF
fi

# 3. Build & start containers
echo "==> Building and launching NeoERP containers..."
$DOCKER_COMPOSE down --remove-orphans || true
$DOCKER_COMPOSE up -d --build

# 4. Install PHP dependencies inside app container
echo "==> Installing Composer dependencies (production)..."
$DOCKER_COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction

# 5. Generate application key if missing
if ! grep -q "APP_KEY=base64:" .env; then
    echo "==> Generating Laravel application key..."
    $DOCKER_COMPOSE exec -T app php artisan key:generate --force
fi

# 6. Wait for MySQL to become ready
echo "==> Waiting for MySQL database to initialize..."
RETRIES=30
until $DOCKER_COMPOSE exec -T db mysqladmin ping -h localhost -u neoerp_user -pneoerp_secret --silent || [ $RETRIES -eq 0 ]; do
    echo "   ...waiting for database ($RETRIES retries left)"
    sleep 3
    RETRIES=$((RETRIES - 1))
done

if [ $RETRIES -eq 0 ]; then
    echo "Error: MySQL failed to initialize in time."
    exit 1
fi
echo "==> Database connection established."

# 7. Run database migrations & showcase seed data
echo "==> Running database migrations..."
$DOCKER_COMPOSE exec -T app php artisan migrate --force

echo "==> Seeding full commercial demo showcase data (100 enquiries, active jobs, live WIP portfolio, plant hires)..."
$DOCKER_COMPOSE exec -T app php artisan db:seed --force

# 8. Create storage symlink & optimize
echo "==> Creating storage symlink..."
$DOCKER_COMPOSE exec -T app php artisan storage:link || true

echo "==> Optimizing configuration, routes, and views..."
$DOCKER_COMPOSE exec -T app php artisan optimize

# 9. Ensure correct permissions on storage and cache
echo "==> Setting directory permissions..."
$DOCKER_COMPOSE exec -T app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
$DOCKER_COMPOSE exec -T app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Detect public IP address
SERVER_IP=$(curl -s -m 5 ifconfig.me || curl -s -m 5 icanhazip.com || hostname -I | awk '{print $1}')

# Update APP_URL in .env with public IP
if [ -n "$SERVER_IP" ]; then
    sed -i "s|APP_URL=http://localhost|APP_URL=http://${SERVER_IP}|g" .env
fi

echo ""
echo "================================================================="
echo "   🎉 NeoERP Showcase Deployment Complete on Google Cloud!"
echo "================================================================="
echo "   Public URL:    http://${SERVER_IP}"
echo ""
echo "   Default Super Admin Login:"
echo "   Email:         admin@neoerp.local"
echo "   Password:      NeoErp!Admin2026#Secure"
echo ""
echo "   Staff / Team Login:"
echo "   Email:         marcus.vance@neoerp.local"
echo "   Password:      NeoErp!Staff2026#"
echo "================================================================="
