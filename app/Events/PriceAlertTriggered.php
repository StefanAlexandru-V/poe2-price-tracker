<?php

namespace App\Events;

use App\Models\Alert;
use App\Models\PriceSnapshot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PriceAlertTriggered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Alert $alert,
        public float $currentPrice,
    ) {}
}
