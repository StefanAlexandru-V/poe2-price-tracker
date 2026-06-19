<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = ['user_id', 'item_id', 'operator', 'threshold', 'active', 'last_triggered_at'];

    protected function casts(): array
    {
        return [
            'threshold' => 'decimal:8',
            'active' => 'boolean',
            'last_triggered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function isTriggered(float $currentPrice): bool
    {
        return match ($this->operator) {
            '<' => $currentPrice < $this->threshold,
            '>' => $currentPrice > $this->threshold,
            '<=' => $currentPrice <= $this->threshold,
            '>=' => $currentPrice >= $this->threshold,
            default => false,
        };
    }
}
