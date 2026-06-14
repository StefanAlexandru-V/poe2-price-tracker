<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
