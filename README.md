# poe2-price-tracker

Tracks PoE2 item prices from poe.ninja. Fetches every 10 min, stores history in postgres, shows graphs.

## Local dev

```bash
cp .env.example .env
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan prices:seed
docker compose exec app php artisan prices:fetch
```

App at http://localhost:8000

## Commands

```
prices:fetch       pull latest from poe.ninja
prices:seed        seed league data
alerts:check       fire notifications for triggered alerts
```

## API

```
GET /api/v1/prices?category=Currency&league=runes-of-aldur
GET /api/v1/prices/{ninja_id}?limit=50
GET /api/v1/prices/{ninja_id}/history?interval=hourly&days=7
```

## Deploy

Push to `main` triggers GitHub Actions -> SSH to Hetzner VPS -> docker compose up.

See `deploy/` for scripts.

## Credits

Price data from [poe.ninja](https://poe.ninja). Not affiliated with Grinding Gear Games.
