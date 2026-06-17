@extends('layouts.app')

@section('title', $item->name . ' — Price History')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}?category={{ $item->category }}&league={{ $league->slug }}" class="text-gray-400 hover:text-white">&larr; Back</a>
        <h1 class="text-xl font-semibold">
            @if($item->icon_url)
            <img src="https://web.poecdn.com{{ $item->icon_url }}" alt="" class="w-8 h-8 inline-block mr-2">
            @endif
            {{ $item->name }}
        </h1>
        <span class="text-sm text-gray-500">{{ $item->category }}</span>
    </div>

    @if($snapshots->isNotEmpty())
    <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
        <div class="flex justify-between items-center mb-4">
            <div>
                <span class="text-2xl font-mono">{{ format_price($snapshots->last()->divine_value) }}</span>
                @php $change = $snapshots->last()->change_7d @endphp
                <span class="text-sm ml-2 {{ ($change ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ format_change($change) }}
                </span>
            </div>
            <span class="text-xs text-gray-500">Last 7 days</span>
        </div>
        <canvas id="priceChart" height="200"></canvas>
    </div>
    @else
    <p class="text-gray-500">No price history available yet.</p>
    @endif
</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('priceChart');
    if (!ctx) return;

    const data = @json($snapshots->map(fn($s) => ['t' => $s->snapshot_at->toIso8601String(), 'y' => (float)$s->divine_value]));

    new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                data: data,
                borderColor: '#af6025',
                backgroundColor: 'rgba(175, 96, 37, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 0,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    type: 'time',
                    time: { unit: 'hour', displayFormats: { hour: 'MMM d HH:mm' } },
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#6b7280' }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#6b7280' }
                }
            },
            parsing: { xAxisKey: 't', yAxisKey: 'y' }
        }
    });
});
</script>
@endpush
