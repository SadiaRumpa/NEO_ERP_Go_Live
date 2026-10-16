#!/bin/bash
# ==============================================================================
# NeoERP — Automated Let's Encrypt Public SSL Certificate Setup
# Issues official, publicly trusted Let's Encrypt certificates directly for
# the public IP (8.228.59.10) or dynamic hostname.
#
# Result: 100% valid SSL padlock in Google Chrome, Safari, Edge, Firefox, iOS,
# and Android without requiring ANY certificate installation on client devices.
# ==============================================================================
set -e

echo "================================================================="
echo "   NeoERP — Public Let's Encrypt SSL Automated Setup"
echo "================================================================="

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# 1. Determine Public IP or Domain
TARGET="${1:-}"
if [ -z "$TARGET" ]; then
    TARGET=$(curl -s -m 5 ifconfig.me || curl -s -m 5 icanhazip.com || hostname -I | awk '{print $1}')
fi
if [ -z "$TARGET" ]; then
    TARGET="8.228.59.10"
fi

EMAIL="${2:-sadiahossain133@gmail.com}"

echo "==> Target Host/IP: $TARGET"
echo "==> Contact Email:  $EMAIL"

# 2. Ensure prerequisites
if command -v apt-get &> /dev/null; then
    sudo apt-get update -qq && sudo apt-get install -y -qq curl cron socat > /dev/null 2>&1 || true
fi

# 3. Ensure challenge directory exists and is writable
WEBROOT="$SCRIPT_DIR/public"
mkdir -p "$WEBROOT/.well-known/acme-challenge"
chmod -R 777 "$WEBROOT/.well-known" 2>/dev/null || true
mkdir -p "$SCRIPT_DIR/docker/nginx/ssl"

# 4. Install acme.sh if not installed
if [ ! -f "$HOME/.acme.sh/acme.sh" ]; then
    echo "==> Installing acme.sh client..."
    curl -fsSL https://get.acme.sh | sh -s email="$EMAIL"
fi
ACME="$HOME/.acme.sh/acme.sh"

# Reset any invalid cached account config
rm -rf "$HOME/.acme.sh/ca/acme-v02.api.letsencrypt.org" 2>/dev/null || true
sed -i '/ACCOUNT_EMAIL/d' "$HOME/.acme.sh/account.conf" 2>/dev/null || true

# Set default CA to Let's Encrypt & register valid account
"$ACME" --set-default-ca --server letsencrypt > /dev/null 2>&1 || true
echo "==> Registering Let's Encrypt account with $EMAIL..."
"$ACME" --register-account -m "$EMAIL" --server letsencrypt || "$ACME" --register-account --server letsencrypt

SUCCESS=0
ACTIVE_HOST=""

# 5. If TARGET is an IP address, attempt shortlived IP cert first
if [[ "$TARGET" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "==> Requesting official Let's Encrypt certificate for IP $TARGET..."
    if "$ACME" --issue \
        --server letsencrypt \
        --cert-profile shortlived \
        --days 3 \
        -d "$TARGET" \
        --webroot "$WEBROOT" \
        --force; then
        
        echo "==> [SUCCESS] Let's Encrypt issued certificate for IP $TARGET!"
        "$ACME" --install-cert -d "$TARGET" \
            --key-file "$SCRIPT_DIR/docker/nginx/ssl/server.key" \
            --fullchain-file "$SCRIPT_DIR/docker/nginx/ssl/server.crt" \
            --reloadcmd "cd $SCRIPT_DIR && docker compose exec -T web nginx -s reload"
        SUCCESS=1
        ACTIVE_HOST="$TARGET"
    else
        echo "==> IP certificate returned notice. Falling back to dynamic hostname..."
    fi
fi

# 6. Fallback / Domain: If not yet issued, request for sslip.io or provided domain
if [ $SUCCESS -eq 0 ]; then
    if [[ "$TARGET" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        DASHED_IP=$(echo "$TARGET" | tr '.' '-')
        FALLBACK_DOMAIN="${DASHED_IP}.sslip.io"
    else
        FALLBACK_DOMAIN="$TARGET"
    fi

    echo "==> Requesting 90-day Let's Encrypt certificate for $FALLBACK_DOMAIN..."
    if "$ACME" --issue \
        --server letsencrypt \
        -d "$FALLBACK_DOMAIN" \
        --webroot "$WEBROOT" \
        --force; then
        
        echo "==> [SUCCESS] Let's Encrypt issued certificate for $FALLBACK_DOMAIN!"
        "$ACME" --install-cert -d "$FALLBACK_DOMAIN" \
            --key-file "$SCRIPT_DIR/docker/nginx/ssl/server.key" \
            --fullchain-file "$SCRIPT_DIR/docker/nginx/ssl/server.crt" \
            --reloadcmd "cd $SCRIPT_DIR && docker compose exec -T web nginx -s reload"
        SUCCESS=1
        ACTIVE_HOST="$FALLBACK_DOMAIN"
    fi
fi

# 7. Reload Nginx and output result
if [ $SUCCESS -eq 1 ]; then
    echo "==> Reloading Nginx container with new certificate..."
    cd "$SCRIPT_DIR"
    docker compose exec -T web nginx -s reload || docker compose restart web

    echo ""
    echo "================================================================="
    echo "   🎉 NeoERP Public SSL Certificate Successfully Active!"
    echo "================================================================="
    echo "   Issued by:     Let's Encrypt Public Certificate Authority"
    echo "   Primary URL:   https://${ACTIVE_HOST}"
    if [ "$ACTIVE_HOST" != "$TARGET" ]; then
        echo "   Direct IP:     https://${TARGET}"
    fi
    echo "   Padlock:       SECURE (Trusted across all browsers globally)"
    echo "================================================================="
else
    echo "==> Error: Could not obtain Let's Encrypt certificate. Checking logs..."
    tail -n 25 "$HOME/.acme.sh/acme.sh.log" 2>/dev/null || true
    exit 1
fi
