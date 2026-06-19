@extends('layouts.app')

@section('title', 'My Alerts')

@section('content')
<div class="flex flex-col gap-6">
    <h1 class="text-xl font-semibold">Price Alerts</h1>

    @if(session('success'))
    <div class="bg-green-900/30 border border-green-800 text-green-300 px-4 py-2 rounded text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Create alert form --}}
    <form method="POST" action="{{ route('alerts.store') }}" class="bg-gray-900 rounded-lg p-4 border border-gray-800">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-xs text-gray-400 block mb-1">Item</label>
                <select name="item_id" required class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-sm">
                    <option value="">Select item...</option>
                    @php $items = \App\Models\Item::forLeague(\App\Models\League::current())->orderBy('name')->get() @endphp
                    @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->category }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-400 block mb-1">Condition</label>
                <select name="operator" class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-sm">
                    <option value="<">drops below</option>
                    <option value=">">rises above</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-400 block mb-1">Threshold (div)</label>
                <input type="number" name="threshold" step="0.0001" required
                       class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-sm">
            </div>
            <button type="submit" class="bg-poe-gold text-black font-medium px-4 py-2 rounded text-sm hover:bg-yellow-600">
                Create Alert
            </button>
        </div>
        @error('threshold') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
    </form>

    {{-- Active alerts --}}
    @if($alerts->isNotEmpty())
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-400 border-b border-gray-800">
                <th class="pb-2">Item</th>
                <th class="pb-2">Condition</th>
                <th class="pb-2">Last Triggered</th>
                <th class="pb-2"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($alerts as $alert)
            <tr class="border-b border-gray-800/50">
                <td class="py-2">{{ $alert->item->name }}</td>
                <td class="py-2 font-mono text-gray-300">{{ $alert->operator }} {{ $alert->threshold }} div</td>
                <td class="py-2 text-gray-500">{{ $alert->last_triggered_at?->diffForHumans() ?? 'Never' }}</td>
                <td class="py-2 text-right">
                    <form method="POST" action="{{ route('alerts.destroy', $alert) }}" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-300 text-xs">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="text-gray-500 text-sm">No alerts yet. Create one above to get notified when prices change.</p>
    @endif
</div>
@endsection
