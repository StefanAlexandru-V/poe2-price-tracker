<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'price' => (float) $this->divine_value,
            'volume' => (float) $this->volume,
            'at' => $this->snapshot_at->toIso8601String(),
        ];
    }
}
