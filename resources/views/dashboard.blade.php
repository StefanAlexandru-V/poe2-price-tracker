@extends('layouts.app')

@section('title', $category . ' - PoE2 Price Tracker')

@section('content')
<div class="flex flex-col gap-6">
    {{-- Category tabs --}}
    <div class="flex flex-wrap gap-2">
        @foreach($categories as $cat)
        <a href="?category={{ $cat }}&league={{ $league->slug }}"
           class="px-3 py-1.5 rounded text-sm {{ $cat === $category ? 'bg-poe-gold text-black font-medium' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
            {{ $cat }}
        </a>
        @endforeach
    </div>

    {{-- Price table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-400 border-b border-gray-800">
                    <th class="pb-2 pl-2">Item</th>
                    <th class="pb-2 text-right">Price</th>
                    <th class="pb-2 text-right">Volume</th>
                    <th class="pb-2 text-right">7d</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr class="border-b border-gray-800/50 hover:bg-gray-800/30">
                    <td class="py-2 pl-2">
                        <a href="{{ route('item.show', $item->slug) }}?league={{ $league->slug }}" class="hover:text-poe-gold">
                            @if($item->icon_url)
                            <img src="https://web.poecdn.com{{ $item->icon_url }}" alt="" class="w-6 h-6 inline-block mr-2">
                            @endif
                            {{ $item->name }}
                        </a>
                    </td>
                    <td class="py-2 text-right font-mono">{{ format_price($item->latestSnapshot->divine_value) }}</td>
                    <td class="py-2 text-right text-gray-400">{{ number_format($item->latestSnapshot->volume, 0) }}</td>
                    <td class="py-2 text-right {{ ($item->latestSnapshot->change_7d ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ format_change($item->latestSnapshot->change_7d) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($items->isEmpty())
    <div class="text-center text-gray-500 py-12">
        No price data yet. Run <code class="text-gray-400">php artisan prices:fetch</code> to populate.
    </div>
    @endif
</div>
@endsection
