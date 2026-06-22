<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\League;
use App\Models\PriceSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPriceTest extends TestCase
{
    use RefreshDatabase;

    private League $league;

    protected function setUp(): void
    {
        parent::setUp();
        $this->league = League::create([
            'name' => 'Test League',
            'slug' => 'test-league',
            'realm' => 'poe2',
            'is_current' => true,
        ]);
    }

    public function test_prices_endpoint_returns_items(): void
    {
        $item = Item::create([
            'ninja_id' => 'divine',
            'name' => 'Divine Orb',
            'slug' => 'divine',
            'category' => 'Currency',
            'league_id' => $this->league->id,
        ]);

        PriceSnapshot::create([
            'item_id' => $item->id,
            'divine_value' => 1.0,
            'volume' => 50000,
            'change_7d' => -3.2,
            'snapshot_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/prices');

        $response->assertOk()
            ->assertJsonPath('league', 'Test League')
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.id', 'divine')
            ->assertJsonPath('items.0.price_divine', '1.00000000');
    }

    public function test_prices_endpoint_filters_by_category(): void
    {
        Item::create(['ninja_id' => 'divine', 'name' => 'Divine', 'slug' => 'divine', 'category' => 'Currency', 'league_id' => $this->league->id]);
        Item::create(['ninja_id' => 'some-rune', 'name' => 'Rune', 'slug' => 'some-rune', 'category' => 'Runes', 'league_id' => $this->league->id]);

        $response = $this->getJson('/api/v1/prices?category=Runes');

        $response->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.category', 'Runes');
    }

    public function test_price_history_endpoint_returns_snapshots(): void
    {
        $item = Item::create([
            'ninja_id' => 'chaos',
            'name' => 'Chaos Orb',
            'slug' => 'chaos',
            'category' => 'Currency',
            'league_id' => $this->league->id,
        ]);

        PriceSnapshot::insert([
            ['item_id' => $item->id, 'divine_value' => 0.12, 'volume' => 100, 'snapshot_at' => now()->subHour()],
            ['item_id' => $item->id, 'divine_value' => 0.11, 'volume' => 90, 'snapshot_at' => now()],
        ]);

        $response = $this->getJson('/api/v1/prices/chaos');

        $response->assertOk()
            ->assertJsonPath('item.name', 'Chaos Orb')
            ->assertJsonCount(2, 'history');
    }

    public function test_price_history_returns_404_for_unknown_item(): void
    {
        $this->getJson('/api/v1/prices/nonexistent')->assertNotFound();
    }
}
