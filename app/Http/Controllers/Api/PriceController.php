<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PriceHistoryResource;
use App\Http\Resources\PriceResource;
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

        $items = $query->get()->filter(fn ($item) => $item->latestSnapshot !== null);

        return response()->json([
            'league' => $league->name,
            'count' => $items->count(),
            'items' => PriceResource::collection($items),
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
            ->get();

        return response()->json([
            'item' => new PriceResource($item->load('latestSnapshot')),
            'history' => PriceHistoryResource::collection($history),
        ]);
    }

    public function aggregated(Request $request, string $ninjaId): JsonResponse
    {
        $league = League::where('slug', $request->get('league'))->first()
            ?? League::current();

        $item = Item::where('ninja_id', $ninjaId)
            ->where('league_id', $league->id)
            ->first();

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $interval = $request->get('interval', 'hourly'); // hourly or daily
        $days = min((int) $request->get('days', 7), 30);

        $history = PriceSnapshot::aggregated($item->id, $interval, $days);

        return response()->json([
            'item' => ['id' => $item->ninja_id, 'name' => $item->name],
            'interval' => $interval,
            'data' => $history,
        ]);
    }
}
