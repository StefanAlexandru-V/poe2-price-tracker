<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = ['ninja_id', 'name', 'slug', 'category', 'icon_url', 'details_id', 'league_id'];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(PriceSnapshot::class);
    }

    public function latestSnapshot()
    {
        return $this->hasOne(PriceSnapshot::class)->latestOfMany('snapshot_at');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeForLeague($query, League $league)
    {
        return $query->where('league_id', $league->id);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
