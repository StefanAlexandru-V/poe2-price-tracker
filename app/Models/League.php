<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class League extends Model
{
    protected $fillable = ['name', 'slug', 'realm', 'is_current', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }
}
