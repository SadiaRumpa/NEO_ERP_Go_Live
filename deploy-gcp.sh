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
    sudo sh get-docker.sh
    rm -f get-docker.sh
    CURRENT_USER="${SUDO_USER:-$USER}"
    if [ -n "$CURRENT_USER" ]; then
        sudo usermod -aG docker "$CURRENT_USER" || true
    fi
    echo "==> Docker installed successfully."
fi

# Ensure docker service is running
if command -v systemctl &> /dev/null; then
    sudo systemctl enable --now docker || true
fi

# Test if docker needs sudo in current session
if docker ps &> /dev/null; then
    DOCKER_CMD="docker"
else
    DOCKER_CMD="sudo docker"
fi

# Determine docker compose command
if $DOCKER_CMD compose version &> /dev/null; then
    DOCKER_COMPOSE="$DOCKER_CMD compose"
elif command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="sudo docker-compose"
else
    echo "==> Installing Docker Compose plugin..."
    sudo apt-get update && sudo apt-get install -y docker-compose-plugin
    DOCKER_COMPOSE="$DOCKER_CMD compose"
fi

# Ensure swap space (prevents Composer / Docker memory errors on smaller cloud VMs)
if [ ! -f /swapfile ] && command -v swapon &> /dev/null; then
    echo "==> Configuring 2GB swap space for stable builds..."
    sudo fallocate -l 2G /swapfile 2>/dev/null || sudo dd if=/dev/zero of=/swapfile bs=1M count=2048 2>/dev/null || true
    sudo chmod 600 /swapfile 2>/dev/null || true
    sudo mkswap /swapfile 2>/dev/null || true
    sudo swapon /swapfile 2>/dev/null || true
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

# 3. Ensure SSL certificate exists for HTTPS (port 443)
if [ ! -f docker/nginx/ssl/server.crt ] || [ ! -f docker/nginx/ssl/server.key ]; then
    echo "==> Generating SSL certificate for HTTPS..."
    mkdir -p docker/nginx/ssl
    openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout docker/nginx/ssl/server.key \
        -out docker/nginx/ssl/server.crt \
        -subj "/C=GB/ST=London/L=London/O=NeoERP/CN=neoerp" \
        -addext "subjectAltName=IP:8.228.59.10,IP:127.0.0.1,DNS:localhost" 2>/dev/null || true
    chmod 600 docker/nginx/ssl/server.key 2>/dev/null || true
fi

# 4. Build & start containers
echo "==> Building and launching NeoERP containers (HTTP :80 & HTTPS :443)..."
$DOCKER_COMPOSE down --remove-orphans || true
$DOCKER_COMPOSE up -d --build

# 5. Install PHP dependencies inside app container
echo "==> Installing Composer dependencies (production)..."
$DOCKER_COMPOSE exec -T app git config --global --add safe.directory /var/www/html 2>/dev/null || true
$DOCKER_COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 6. Generate application key if missing
if ! grep -q "APP_KEY=base64:" .env; then
    echo "==> Generating Laravel application key..."
    $DOCKER_COMPOSE exec -T app php artisan key:generate --force
fi

# 7. Wait for MySQL to become ready
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

# 8. Run database migrations & showcase seed data
echo "==> Running database migrations..."
$DOCKER_COMPOSE exec -T app php artisan migrate --force

echo "==> Seeding full commercial demo showcase data (100 enquiries, active jobs, live WIP portfolio, plant hires)..."
$DOCKER_COMPOSE exec -T app php artisan db:seed --force

# 9. Create storage symlink & optimize
echo "==> Creating storage symlink..."
$DOCKER_COMPOSE exec -T app php artisan storage:link || true

echo "==> Optimizing configuration, routes, and views..."
$DOCKER_COMPOSE exec -T app php artisan optimize

# 10. Ensure correct permissions on storage and cache
echo "==> Setting directory permissions..."
$DOCKER_COMPOSE exec -T app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
$DOCKER_COMPOSE exec -T app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Detect public IP address
SERVER_IP=$(curl -s -m 5 ifconfig.me || curl -s -m 5 icanhazip.com || hostname -I | awk '{print $1}')

# Update APP_URL in .env with public IP
if [ -n "$SERVER_IP" ]; then
    sed -i "s|APP_URL=http://localhost|APP_URL=http://${SERVER_IP}|g" .env
fi

echo ""
echo "================================================================="
echo "   🎉 NeoERP Showcase Deployment Complete on Google Cloud!"
echo "================================================================="
echo "   HTTP URL:      http://${SERVER_IP}"
echo "   HTTPS URL:     https://${SERVER_IP}"
echo ""
echo "   Default Super Admin Login:"
echo "   Email:         admin@neoerp.local"
echo "   Password:      NeoErp!Admin2026#Secure"
echo ""
echo "   Staff / Team Login:"
echo "   Email:         marcus.vance@neoerp.local"
echo "   Password:      NeoErp!Staff2026#"
echo "================================================================="
