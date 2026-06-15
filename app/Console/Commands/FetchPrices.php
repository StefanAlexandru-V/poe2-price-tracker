<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\League;
use App\Models\PriceSnapshot;
use App\Services\PoeNinjaClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FetchPrices extends Command
{
    protected $signature = 'prices:fetch {--league= : League name (defaults to current)}';
    protected $description = 'Fetch current prices from poe.ninja and store snapshots';

    public function handle(PoeNinjaClient $client): int
    {
        $league = $this->resolveLeague();
        if (!$league) {
            $this->error('No current league set. Run prices:seed first.');
            return self::FAILURE;
        }

        $this->info("Fetching prices for {$league->name}...");

        $results = $client->fetchAll($league->name);

        if (empty($results)) {
            $this->warn('No data returned from poe.ninja. API might be down.');
            return self::FAILURE;
        }

        $now = now();
        $created = 0;
        $snapshots = [];

        foreach ($results as $type => $data) {
            $meta = $client->extractItemMeta($data);

            foreach ($data['lines'] as $line) {
                $ninjaId = $line['id'];

                $item = $this->findOrCreateItem($ninjaId, $type, $meta, $league);

                $snapshots[] = [
                    'item_id' => $item->id,
                    'divine_value' => $line['primaryValue'],
                    'volume' => $line['volumePrimaryValue'] ?? 0,
                    'change_7d' => $line['sparkline']['totalChange'] ?? null,
                    'snapshot_at' => $now,
                ];
                $created++;
            }
        }

        // bulk insert in chunks
        foreach (array_chunk($snapshots, 200) as $chunk) {
            PriceSnapshot::insert($chunk);
        }

        $this->info("Stored {$created} price snapshots across " . count($results) . " categories.");

        return self::SUCCESS;
    }

    private function resolveLeague(): ?League
    {
        if ($name = $this->option('league')) {
            return League::where('name', $name)->orWhere('slug', $name)->first();
        }
        return League::current();
    }

    private function findOrCreateItem(string $ninjaId, string $category, array $meta, League $league): Item
    {
        $existing = Item::where('ninja_id', $ninjaId)
            ->where('league_id', $league->id)
            ->where('category', $category)
            ->first();

        if ($existing) {
            return $existing;
        }

        // poe.ninja only gives display names for a handful of reference currencies
        // the rest we have to humanize from the slug
        $metaEntry = $meta[$ninjaId] ?? null;
        $name = $metaEntry['name'] ?? $this->humanize($ninjaId);

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

    private function humanize(string $slug): string
    {
        return Str::of($slug)->replace('-', ' ')->title()->toString();
    }
}
