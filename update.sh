#!/bin/bash
# ==============================================================================
# NeoERP — Instant Pull & Update Script for Google Cloud VM
# ==============================================================================
set -e

echo "================================================================="
echo "   Updating NeoERP to latest GitHub code..."
echo "================================================================="

# 1. Pull latest code
git pull origin main

# 2. Determine docker compose command (with or without sudo)
if docker ps &> /dev/null; then
    DOCKER_CMD="docker"
else
    DOCKER_CMD="sudo docker"
fi

if $DOCKER_CMD compose version &> /dev/null; then
    DOCKER_COMPOSE="$DOCKER_CMD compose"
else
    DOCKER_COMPOSE="sudo docker-compose"
fi

# 3. Update dependencies & database
echo "==> Updating composer dependencies..."
$DOCKER_COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Running any pending migrations..."
$DOCKER_COMPOSE exec -T app php artisan migrate --force

echo "==> Clearing and refreshing view/route/config caches..."
$DOCKER_COMPOSE exec -T app php artisan optimize:clear
$DOCKER_COMPOSE exec -T app php artisan optimize

echo "==> Restarting web & app services..."
$DOCKER_COMPOSE restart app web

echo "================================================================="
echo "   ✅ NeoERP is now running the latest version from GitHub!"
echo "================================================================="
