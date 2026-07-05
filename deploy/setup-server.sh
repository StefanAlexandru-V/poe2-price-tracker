#!/usr/bin/env bash
# One-time server setup for poe2-price-tracker.
# Usage: ssh root@<SERVER_HOST> 'bash -s' < deploy/setup-server.sh
set -euo pipefail

echo "==> Installing Docker"
if ! command -v docker &>/dev/null; then
  apt-get update -qq
  apt-get install -y -qq ca-certificates curl gnupg
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
  chmod a+r /etc/apt/keyrings/docker.gpg
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
  apt-get update -qq
  apt-get install -y -qq docker-ce docker-ce-cli containerd.io docker-compose-plugin
fi
echo "Docker: $(docker --version)"

echo "==> Creating app directory"
mkdir -p /opt/poe2-prices

echo "==> Adding Caddy site"
cat >> /etc/caddy/Caddyfile << 'CADDY'

poe2-prices.stefanvladu.dev {
    reverse_proxy localhost:8000
}
CADDY

echo "==> Reloading Caddy"
systemctl reload caddy

echo "==> Writing .env"
if [ ! -f /opt/poe2-prices/.env ]; then
  APP_KEY=$(openssl rand -base64 32)
  cat > /opt/poe2-prices/.env << EOF
APP_NAME="PoE2 Price Tracker"
APP_ENV=production
APP_KEY=base64:${APP_KEY}
APP_DEBUG=false
APP_URL=https://poe2-prices.stefanvladu.dev

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=sqlite

SESSION_DRIVER=redis
SESSION_LIFETIME=120

QUEUE_CONNECTION=redis
CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
EOF
  echo "Generated .env"
fi

echo "==> Server setup complete"
echo "    Next: push to main to trigger deployment"
