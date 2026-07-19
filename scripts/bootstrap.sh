#!/bin/sh
set -eu

# This script runs on the Ubuntu host once, before Docker Compose parses .env.
# It never overwrites an existing environment file or generates payment secrets.
if [ -f .env ]; then
    echo ".env already exists; no changes were made."
    exit 0
fi

if ! command -v openssl >/dev/null 2>&1; then
    echo >&2 "ERROR: openssl is required. Install it with: sudo apt-get install -y openssl"
    exit 1
fi

cp .env.docker.example .env

APP_KEY_VALUE="base64:$(openssl rand -base64 32 | tr -d '\n')"
MYSQL_APP_PASSWORD="$(openssl rand -hex 24)"
MYSQL_ROOT_PASSWORD_VALUE="$(openssl rand -hex 32)"

sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY_VALUE}|" .env
sed -i "s|^MYSQL_PASSWORD=.*|MYSQL_PASSWORD=${MYSQL_APP_PASSWORD}|" .env
sed -i "s|^MYSQL_ROOT_PASSWORD=.*|MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD_VALUE}|" .env

chmod 600 .env

echo ".env created successfully."
echo "APP_KEY and MySQL passwords were generated securely."
echo "Midtrans keys remain empty; use the Local Simulator or add real Sandbox keys later."
