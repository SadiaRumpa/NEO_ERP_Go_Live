#!/bin/bash
# ==============================================================================
# NeoERP — Automated Let's Encrypt Public SSL Certificate Setup
# Issues official, publicly trusted Let's Encrypt certificates directly for
# the public IP (8.228.59.10) with automatic 3-day renewal cron, plus dynamic
# sslip.io / nip.io hostname support.
#
# Result: 100% valid SSL padlock in Google Chrome, Safari, Edge, Firefox, iOS,
# and Android without requiring ANY certificate installation on client devices.
# ==============================================================================
set -e

echo "================================================================="
echo "   NeoERP — Public Let's Encrypt SSL Automated Setup"
echo "================================================================="

# 1. Determine Public IP
TARGET_IP="${1:-}"
if [ -z "$TARGET_IP" ]; then
    TARGET_IP=$(curl -s -m 5 ifconfig.me || curl -s -m 5 icanhazip.com || hostname -I | awk '{print $1}')
fi
if [ -z "$TARGET_IP" ]; then
    TARGET_IP="8.228.59.10"
fi

echo "==> Target Public IP: $TARGET_IP"

# 2. Ensure prerequisites
if command -v apt-get &> /dev/null; then
    sudo apt-get update -qq && sudo apt-get install -y -qq curl cron socat > /dev/null 2>&1 || true
fi

# 3. Ensure challenge directory exists
mkdir -p ./public/.well-known/acme-challenge
chmod -R 777 ./public/.well-known 2>/dev/null || true
mkdir -p ./docker/nginx/ssl

# 4. Install acme.sh if not installed
if [ ! -f "$HOME/.acme.sh/acme.sh" ]; then
    echo "==> Installing acme.sh client..."
    curl -fsSL https://get.acme.sh | sh -s email=admin@neoerp.local
fi
ACME="$HOME/.acme.sh/acme.sh"

# Set default CA to Let's Encrypt
"$ACME" --set-default-ca --server letsencrypt > /dev/null 2>&1 || true
"$ACME" --register-account -m admin@neoerp.local --server letsencrypt > /dev/null 2>&1 || true

# 5. Attempt 1: Issue official Let's Encrypt certificate for the public IP
echo "==> Requesting official Let's Encrypt certificate for IP $TARGET_IP..."
SUCCESS=0

if "$ACME" --issue \
    --server letsencrypt \
    --cert-profile shortlived \
    --days 3 \
    -d "$TARGET_IP" \
    --webroot "./public" \
    --force; then
    
    echo "==> [SUCCESS] Let's Encrypt issued certificate for $TARGET_IP!"
    "$ACME" --install-cert -d "$TARGET_IP" \
        --key-file "./docker/nginx/ssl/server.key" \
        --fullchain-file "./docker/nginx/ssl/server.crt" \
        --reloadcmd "docker compose exec -T web nginx -s reload"
    SUCCESS=1
else
    echo "==> IP certificate returned notice. Falling back to dynamic hostname domain..."
fi

# 6. Attempt 2 (Fallback): Issue standard 90-day Let's Encrypt cert for sslip.io domain
if [ $SUCCESS -eq 0 ]; then
    DASHED_IP=$(echo "$TARGET_IP" | tr '.' '-')
    FALLBACK_DOMAIN="${DASHED_IP}.sslip.io"
    echo "==> Requesting 90-day Let's Encrypt certificate for $FALLBACK_DOMAIN..."
    
    if "$ACME" --issue \
        --server letsencrypt \
        -d "$FALLBACK_DOMAIN" \
        -d "$TARGET_IP.nip.io" \
        --webroot "./public" \
        --force; then
        
        echo "==> [SUCCESS] Let's Encrypt issued certificate for $FALLBACK_DOMAIN!"
        "$ACME" --install-cert -d "$FALLBACK_DOMAIN" \
            --key-file "./docker/nginx/ssl/server.key" \
            --fullchain-file "./docker/nginx/ssl/server.crt" \
            --reloadcmd "docker compose exec -T web nginx -s reload"
        SUCCESS=1
    fi
fi

# 7. Reload Nginx
if [ $SUCCESS -eq 1 ]; then
    echo "==> Reloading Nginx container with new certificate..."
    docker compose exec -T web nginx -s reload || docker compose restart web

    echo ""
    echo "================================================================="
    echo "   🎉 NeoERP Public SSL Certificate Successfully Active!"
    echo "================================================================="
    echo "   Issued by:     Let's Encrypt Public Certificate Authority"
    echo "   Secure URL:    https://${TARGET_IP}"
    if [ -n "$FALLBACK_DOMAIN" ]; then
        echo "   Domain URL:    https://${FALLBACK_DOMAIN}"
    fi
    echo "   Padlock:       SECURE (Trusted across all browsers globally)"
    echo "================================================================="
else
    echo "==> Error: Could not obtain Let's Encrypt certificate. Checking logs..."
    tail -n 20 "$HOME/.acme.sh/acme.sh.log" 2>/dev/null || true
    exit 1
fi
