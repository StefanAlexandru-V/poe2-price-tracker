<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PoeNinjaClient
{
    private string $baseUrl = 'https://poe.ninja/poe2/api/economy/exchange/current/overview';

    private array $categories = [
        'Currency',
        'Fragments',
        'Essences',
        'SoulCores',
        'Idols',
        'Runes',
        'Omens',
        'Expedition',
        'LiquidEmotions',
        'Catalysts',
        'Verisium',
        'Abyss',
    ];

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function fetchCategory(string $league, string $type): ?array
    {
        $cacheKey = "poeninja.{$league}.{$type}";

        // don't hammer them - cache for 8 minutes (we fetch every 10)
        return Cache::remember($cacheKey, 480, function () use ($league, $type) {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'poe2-price-tracker/1.0 (personal project)'])
                ->get($this->baseUrl, [
                    'league' => $league,
                    'type' => $type,
                ]);

            if ($response->failed()) {
                Log::warning("[PoeNinja] Failed to fetch {$type}", [
                    'status' => $response->status(),
                    'league' => $league,
                ]);
                return null;
            }

            return $response->json();
        });
    }

    /**
     * Fetch all categories for a league. Returns [type => response] map.
     */
    public function fetchAll(string $league): array
    {
        $results = [];

        foreach ($this->categories as $type) {
            $data = $this->fetchCategory($league, $type);
            if ($data && !empty($data['lines'])) {
                $results[$type] = $data;
            }

            // be nice
            usleep(200_000);
        }

        return $results;
    }

    /**
     * Extract item metadata from the core.items array in a response.
     * poe.ninja only includes reference currencies there (divine, chaos, exalted),
     * so most items just have a slug id with no display name in this endpoint.
     */
    public function extractItemMeta(array $response): array
    {
        $meta = [];
        foreach ($response['core']['items'] ?? [] as $item) {
            $meta[$item['id']] = [
                'name' => $item['name'],
                'image' => $item['image'] ?? null,
                'details_id' => $item['detailsId'] ?? null,
            ];
        }
        return $meta;
    }
}
