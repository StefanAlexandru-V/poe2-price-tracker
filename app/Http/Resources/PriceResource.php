<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ninja_id,
            'name' => $this->name,
            'category' => $this->category,
            'icon' => $this->icon_url ? 'https://web.poecdn.com' . $this->icon_url : null,
            'price' => [
                'divine' => $this->whenLoaded('latestSnapshot', fn () => (float) $this->latestSnapshot?->divine_value),
                'volume' => $this->whenLoaded('latestSnapshot', fn () => (float) $this->latestSnapshot?->volume),
                'change_7d' => $this->whenLoaded('latestSnapshot', fn () => $this->latestSnapshot?->change_7d),
            ],
            'updated_at' => $this->whenLoaded('latestSnapshot', fn () => $this->latestSnapshot?->snapshot_at?->toIso8601String()),
        ];
    }
}
