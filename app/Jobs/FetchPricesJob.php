<?php

namespace App\Jobs;

use App\Models\Item;
use App\Models\League;
use App\Models\PriceSnapshot;
use App\Services\PoeNinjaClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 60;

    public function __construct(public ?string $leagueSlug = null)
    {
    }

    public function handle(PoeNinjaClient $client): void
    {
        $league = $this->leagueSlug
            ? League::where('slug', $this->leagueSlug)->first()
            : League::current();

        if (!$league) {
            Log::error('[FetchPricesJob] No league found');
            return;
        }

        Log::info("[FetchPricesJob] Starting for {$league->name}");

        $results = $client->fetchAll($league->name);

        if (empty($results)) {
            Log::warning('[FetchPricesJob] No data from poe.ninja');
            return;
        }

        $now = now();
        $snapshots = [];

        foreach ($results as $type => $data) {
            $meta = $client->extractItemMeta($data);

            foreach ($data['lines'] as $line) {
                $item = $this->findOrCreateItem($line['id'], $type, $meta, $league);

                $snapshots[] = [
                    'item_id' => $item->id,
                    'divine_value' => $line['primaryValue'],
                    'volume' => $line['volumePrimaryValue'] ?? 0,
                    'change_7d' => $line['sparkline']['totalChange'] ?? null,
                    'snapshot_at' => $now,
                ];
            }
        }

        foreach (array_chunk($snapshots, 200) as $chunk) {
            PriceSnapshot::insert($chunk);
        }

        Log::info("[FetchPricesJob] Stored " . count($snapshots) . " snapshots");
    }

    private function findOrCreateItem(string $ninjaId, string $category, array $meta, League $league): Item
    {
        $existing = Item::where('ninja_id', $ninjaId)
            ->where('league_id', $league->id)
            ->where('category', $category)
            ->first();

        if ($existing) {
            $metaEntry = $meta[$ninjaId] ?? null;
            if ($metaEntry && !$existing->icon_url && $metaEntry['image']) {
                $existing->update([
                    'icon_url' => $metaEntry['image'],
                    'name' => $metaEntry['name'] ?? $existing->name,
                ]);
            }
            return $existing;
        }

        $metaEntry = $meta[$ninjaId] ?? null;
        $name = $metaEntry['name'] ?? Str::of($ninjaId)->replace('-', ' ')->title()->toString();

        return Item::create([
            'ninja_id' => $ninjaId,
            'name' => $name,
            'slug' => Str::slug($ninjaId),
            'category' => $category,
            'icon_url' => $metaEntry['image'] ?? null,
            'details_id' => $metaEntry['details_id'] ?? $ninjaId,
            'league_id' => $league->id,
        ]);
    }
}
