<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\League;
use App\Models\PriceSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $league = League::where('slug', $request->get('league'))->first()
            ?? League::current();

        if (!$league) {
            return response()->json(['error' => 'No league found'], 404);
        }

        $query = Item::with('latestSnapshot')
            ->forLeague($league);

        if ($category = $request->get('category')) {
            $query->category($category);
        }

        $items = $query->get()->map(fn ($item) => [
            'id' => $item->ninja_id,
            'name' => $item->name,
            'category' => $item->category,
            'price_divine' => $item->latestSnapshot?->divine_value,
            'volume' => $item->latestSnapshot?->volume,
            'change_7d' => $item->latestSnapshot?->change_7d,
            'updated_at' => $item->latestSnapshot?->snapshot_at?->toIso8601String(),
        ]);

        return response()->json([
            'league' => $league->name,
            'count' => $items->count(),
            'items' => $items->values(),
        ]);
    }

    public function show(Request $request, string $ninjaId): JsonResponse
    {
        $league = League::where('slug', $request->get('league'))->first()
            ?? League::current();

        $item = Item::where('ninja_id', $ninjaId)
            ->where('league_id', $league->id)
            ->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $history = PriceSnapshot::forItem($item->id)
            ->orderByDesc('snapshot_at')
            ->limit($request->get('limit', 100))
            ->get()
            ->map(fn ($s) => [
                'price' => $s->divine_value,
                'volume' => $s->volume,
                'at' => $s->snapshot_at->toIso8601String(),
            ]);

        return response()->json([
            'item' => [
                'id' => $item->ninja_id,
                'name' => $item->name,
                'category' => $item->category,
            ],
            'history' => $history,
        ]);
    }
}
