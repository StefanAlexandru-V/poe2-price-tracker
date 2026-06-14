# poe2-price-tracker

PoE2 economy tracker. Fetches prices from poe.ninja every 10 minutes, stores history, shows trends.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan prices:seed
php artisan prices:fetch
php artisan serve
```

## Commands

```bash
php artisan prices:fetch          # fetch latest prices
php artisan prices:seed           # seed league data
php artisan alerts:check          # check price alerts
```

## API

```
GET /api/v1/prices?category=Currency&league=runes-of-aldur
GET /api/v1/prices/{ninja_id}?limit=50
```

## Stack

- Laravel 13, PHP 8.3
- SQLite (dev) / PostgreSQL (prod)
- Redis for cache + queue
- Tailwind (CDN) + Alpine.js
- Chart.js for price graphs
- poe.ninja API (no auth required)

## Data source

Prices from [poe.ninja](https://poe.ninja) — not affiliated with Grinding Gear Games.
