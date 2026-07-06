#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/opt/poe2-prices"
cd "$APP_DIR"

echo "--- Pulling latest code"
if [ ! -d .git ]; then
    git clone https://github.com/StefanAlexandru-V/poe2-price-tracker.git /tmp/poe2-src
    cp -r /tmp/poe2-src/. .
    rm -rf /tmp/poe2-src
else
    git pull origin main
fi

echo "--- Building and starting containers"
docker compose build --quiet
docker compose up -d

echo "--- Waiting for postgres"
sleep 8

echo "--- Running migrations"
docker compose exec -T app php artisan migrate --force

echo "--- Seeding leagues"
docker compose exec -T app php artisan prices:seed

echo "--- Fetching initial prices"
docker compose exec -T app php artisan prices:fetch

echo "--- Health check"
sleep 3
if curl -sf http://localhost:8000/api/v1/prices > /dev/null; then
    echo "Deployment successful!"
else
    echo "FAILED, container logs:"
    docker compose logs app --tail=30
    exit 1
fi
