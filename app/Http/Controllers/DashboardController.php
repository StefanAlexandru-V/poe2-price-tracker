<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\League;
use App\Models\PriceSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $league = $this->resolveLeague($request);
        $category = $request->get('category', 'Currency');

        // prices only update every 10 min, no point querying every page load
        $cacheKey = "dashboard.{$league->id}.{$category}";
        $items = Cache::remember($cacheKey, 300, function () use ($league, $category) {
            return Item::with('latestSnapshot')
                ->forLeague($league)
                ->category($category)
                ->get()
                ->filter(fn ($item) => $item->latestSnapshot !== null)
                ->sortByDesc(fn ($item) => $item->latestSnapshot->volume);
        });

        $categories = Cache::remember("categories.{$league->id}", 600, function () use ($league) {
            return Item::forLeague($league)
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->sort()
                ->values()
                ->all();
        });

        $leagues = League::orderByDesc('is_current')->get();

        return view('dashboard', compact('items', 'categories', 'category', 'league', 'leagues'));
    }

    public function show(Request $request, string $slug)
    {
        $league = $this->resolveLeague($request);

        $item = Item::where('slug', $slug)
            ->where('league_id', $league->id)
            ->firstOrFail();

        $snapshots = PriceSnapshot::forItem($item->id)
            ->orderBy('snapshot_at')
            ->where('snapshot_at', '>=', now()->subDays(7))
            ->get();

        return view('item-detail', compact('item', 'snapshots', 'league'));
    }

    private function resolveLeague(Request $request): League
    {
        if ($slug = $request->get('league')) {
            return League::where('slug', $slug)->firstOrFail();
        }
        return League::current() ?? League::first();
    }
}
