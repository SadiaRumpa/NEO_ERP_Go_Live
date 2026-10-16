#!/bin/bash
# ==============================================================================
# NeoERP — 2-Tier Enterprise SSL Certificate Generator for IP Addresses
# Generates a Root Certificate Authority (CA) + Leaf Server Certificate with
# Subject Alternative Name (SAN) and TLS Server Authentication for Chrome/Edge.
# ==============================================================================
set -e

SSL_DIR="docker/nginx/ssl"
mkdir -p "$SSL_DIR"

# 1. Determine Target IP Address
TARGET_IP="${1:-}"
if [ -z "$TARGET_IP" ]; then
    TARGET_IP=$(curl -s -m 5 ifconfig.me || curl -s -m 5 icanhazip.com || hostname -I | awk '{print $1}')
fi
if [ -z "$TARGET_IP" ]; then
    TARGET_IP="8.228.59.10"
fi

echo "==> Configuring SSL for Target IP: $TARGET_IP"

# 2. Generate Root CA if not exists
if [ ! -f "$SSL_DIR/neoerp-root-ca.key" ] || [ ! -f "$SSL_DIR/neoerp-root-ca.crt" ]; then
    echo "==> Generating NeoERP Root Certificate Authority..."
    openssl genrsa -out "$SSL_DIR/neoerp-root-ca.key" 2048

    cat << 'EOF' > "$SSL_DIR/ca.cnf"
[ req ]
distinguished_name = req_distinguished_name
prompt = no
x509_extensions = v3_ca

[ req_distinguished_name ]
C = GB
ST = London
L = London
O = NeoERP Cloud Enterprise
CN = NeoERP Root Certificate Authority

[ v3_ca ]
basicConstraints = critical, CA:TRUE
keyUsage = critical, digitalSignature, cRLSign, keyCertSign
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid:always,issuer
EOF

    openssl req -x509 -new -nodes -key "$SSL_DIR/neoerp-root-ca.key" \
        -sha256 -days 3650 -out "$SSL_DIR/neoerp-root-ca.crt" -config "$SSL_DIR/ca.cnf"
    rm -f "$SSL_DIR/ca.cnf"
fi

# 3. Generate Server Leaf Private Key & CSR
echo "==> Generating Server Leaf Certificate with IP SAN..."
openssl genrsa -out "$SSL_DIR/server.key" 2048
chmod 600 "$SSL_DIR/server.key"

cat << EOF > "$SSL_DIR/server.cnf"
[ req ]
distinguished_name = req_distinguished_name
prompt = no
req_extensions = v3_req

[ req_distinguished_name ]
C = GB
ST = London
L = London
O = NeoERP Cloud Enterprise
CN = $TARGET_IP

[ v3_req ]
basicConstraints = CA:FALSE
keyUsage = critical, digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @alt_names

[ alt_names ]
IP.1 = $TARGET_IP
IP.2 = 8.228.59.10
IP.3 = 127.0.0.1
DNS.1 = localhost
DNS.2 = *.nip.io
DNS.3 = *.sslip.io
EOF

openssl req -new -key "$SSL_DIR/server.key" -out "$SSL_DIR/server.csr" -config "$SSL_DIR/server.cnf"

# 4. Sign Leaf Certificate using Root CA
cat << EOF > "$SSL_DIR/server.ext"
basicConstraints = CA:FALSE
keyUsage = critical, digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid,issuer
subjectAltName = @alt_names

[ alt_names ]
IP.1 = $TARGET_IP
IP.2 = 8.228.59.10
IP.3 = 127.0.0.1
DNS.1 = localhost
DNS.2 = *.nip.io
DNS.3 = *.sslip.io
EOF

openssl x509 -req -in "$SSL_DIR/server.csr" \
    -CA "$SSL_DIR/neoerp-root-ca.crt" \
    -CAkey "$SSL_DIR/neoerp-root-ca.key" \
    -CAcreateserial -out "$SSL_DIR/server-leaf.crt" \
    -days 1095 -sha256 -extfile "$SSL_DIR/server.ext"

# 5. Build full chain for Nginx
cat "$SSL_DIR/server-leaf.crt" "$SSL_DIR/neoerp-root-ca.crt" > "$SSL_DIR/server.crt"

# 6. Cleanup temporary artifacts
rm -f "$SSL_DIR/server.cnf" "$SSL_DIR/server.csr" "$SSL_DIR/server.ext" "$SSL_DIR/server-leaf.crt"

# 7. Copy Root CA to public folder for direct browser download
mkdir -p public
cp -f "$SSL_DIR/neoerp-root-ca.crt" public/neoerp-root-ca.crt

echo "================================================================="
echo "   [SUCCESS] NeoERP SSL Certificates Generated Successfully!"
echo "   Target IP:     $TARGET_IP"
echo "   Root CA:       $SSL_DIR/neoerp-root-ca.crt"
echo "   Public CA:     public/neoerp-root-ca.crt"
echo "   Server Cert:   $SSL_DIR/server.crt"
echo "   Server Key:    $SSL_DIR/server.key"
echo "================================================================="
