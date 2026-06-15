<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\League;
use App\Models\PriceSnapshot;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $league = $this->resolveLeague($request);
        $category = $request->get('category', 'Currency');

        $items = Item::with('latestSnapshot')
            ->forLeague($league)
            ->category($category)
            ->get()
            ->filter(fn ($item) => $item->latestSnapshot !== null)
            ->sortByDesc(fn ($item) => $item->latestSnapshot->volume);

        $categories = Item::forLeague($league)
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

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
