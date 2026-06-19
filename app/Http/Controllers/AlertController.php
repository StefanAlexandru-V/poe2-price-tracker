<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Item;
use App\Models\League;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = Alert::with('item')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return view('alerts.index', compact('alerts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'operator' => 'required|in:<,>,<=,>=',
            'threshold' => 'required|numeric|min:0',
        ]);

        Alert::create([
            'user_id' => $request->user()->id,
            'item_id' => $validated['item_id'],
            'operator' => $validated['operator'],
            'threshold' => $validated['threshold'],
        ]);

        return back()->with('success', 'Alert created.');
    }

    public function destroy(Request $request, Alert $alert)
    {
        if ($alert->user_id !== $request->user()->id) {
            abort(403);
        }

        $alert->delete();
        return back()->with('success', 'Alert deleted.');
    }
}
