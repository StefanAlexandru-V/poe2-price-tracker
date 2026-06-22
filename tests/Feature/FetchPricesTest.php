<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\League;
use App\Models\PriceSnapshot;
use App\Services\PoeNinjaClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchPricesTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_prices_creates_items_and_snapshots(): void
    {
        $league = League::create([
            'name' => 'Test League',
            'slug' => 'test-league',
            'realm' => 'poe2',
            'is_current' => true,
        ]);

        Http::fake([
            'poe.ninja/*' => Http::response([
                'core' => [
                    'items' => [
                        ['id' => 'divine', 'name' => 'Divine Orb', 'image' => '/img/divine.png', 'detailsId' => 'divine-orb'],
                    ],
                ],
                'lines' => [
                    [
                        'id' => 'divine',
                        'primaryValue' => 1.0,
                        'volumePrimaryValue' => 50000,
                        'sparkline' => ['totalChange' => -5.2, 'data' => []],
                    ],
                    [
                        'id' => 'chaos',
                        'primaryValue' => 0.119,
                        'volumePrimaryValue' => 200000,
                        'sparkline' => ['totalChange' => 12.0, 'data' => []],
                    ],
                ],
                'items' => [],
            ]),
        ]);

        $this->artisan('prices:fetch')
            ->expectsOutputToContain('Stored')
            ->assertSuccessful();

        $this->assertDatabaseCount('items', 2);
        $this->assertDatabaseCount('price_snapshots', 2);

        $divine = Item::where('ninja_id', 'divine')->first();
        $this->assertEquals('Divine Orb', $divine->name);
        $this->assertEquals('/img/divine.png', $divine->icon_url);

        $chaos = Item::where('ninja_id', 'chaos')->first();
        $this->assertEquals('Chaos', $chaos->name); // humanized from slug
    }

    public function test_fetch_prices_fails_gracefully_when_no_league(): void
    {
        $this->artisan('prices:fetch')
            ->expectsOutputToContain('No current league')
            ->assertFailed();
    }

    public function test_poe_ninja_client_caches_responses(): void
    {
        Http::fake([
            'poe.ninja/*' => Http::response(['core' => ['items' => []], 'lines' => [], 'items' => []]),
        ]);

        $client = new PoeNinjaClient();
        $client->fetchCategory('Test League', 'Currency');
        $client->fetchCategory('Test League', 'Currency');

        Http::assertSentCount(1);
    }
}
