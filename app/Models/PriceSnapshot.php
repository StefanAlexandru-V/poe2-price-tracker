<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PriceSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = ['item_id', 'divine_value', 'volume', 'change_7d', 'snapshot_at'];

    protected function casts(): array
    {
        return [
            'divine_value' => 'decimal:8',
            'volume' => 'decimal:2',
            'change_7d' => 'decimal:2',
            'snapshot_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('snapshot_at', '>=', now()->subHours($hours));
    }

    public function scopeForItem($query, int $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Aggregated price data grouped by time interval.
     * Returns avg/min/max price and total volume per bucket.
     */
    public static function aggregated(int $itemId, string $interval = 'hourly', int $days = 7): array
    {
        $since = now()->subDays($days);

        $truncExpr = match ($interval) {
            'daily' => "date_trunc('day', snapshot_at)",
            default => "date_trunc('hour', snapshot_at)",
        };

        return DB::select("
            SELECT
                {$truncExpr} as bucket,
                ROUND(AVG(divine_value)::numeric, 8) as avg_price,
                ROUND(MIN(divine_value)::numeric, 8) as min_price,
                ROUND(MAX(divine_value)::numeric, 8) as max_price,
                ROUND(SUM(volume)::numeric, 2) as total_volume,
                COUNT(*) as sample_count
            FROM price_snapshots
            WHERE item_id = ? AND snapshot_at >= ?
            GROUP BY bucket
            ORDER BY bucket
        ", [$itemId, $since]);
    }
}
