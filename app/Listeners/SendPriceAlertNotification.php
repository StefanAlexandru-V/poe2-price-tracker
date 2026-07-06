<?php

namespace App\Listeners;

use App\Events\PriceAlertTriggered;
use App\Notifications\PriceAlertNotification;

class SendPriceAlertNotification
{
    public function handle(PriceAlertTriggered $event): void
    {
        $event->alert->user->notify(
            new PriceAlertNotification($event->alert, $event->currentPrice)
        );

        $event->alert->update(['last_triggered_at' => now()]);
    }
}
