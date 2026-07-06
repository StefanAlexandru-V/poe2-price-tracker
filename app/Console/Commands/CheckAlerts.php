<?php

namespace App\Console\Commands;

use App\Events\PriceAlertTriggered;
use App\Models\Alert;
use Illuminate\Console\Command;

class CheckAlerts extends Command
{
    protected $signature = 'alerts:check';
    protected $description = 'Check active alerts against latest prices and dispatch notifications';

    public function handle(): int
    {
        $alerts = Alert::with(['item.latestSnapshot', 'user'])
            ->where('active', true)
            ->get();

        if ($alerts->isEmpty()) {
            $this->line('No active alerts.');
            return self::SUCCESS;
        }

        $triggered = 0;

        foreach ($alerts as $alert) {
            $snapshot = $alert->item->latestSnapshot;
            if (!$snapshot) continue;

            $price = (float) $snapshot->divine_value;

            if (!$alert->isTriggered($price)) continue;

            // don't spam, skip if triggered in the last hour
            if ($alert->last_triggered_at && $alert->last_triggered_at->gt(now()->subHour())) {
                continue;
            }

            PriceAlertTriggered::dispatch($alert, $price);
            $triggered++;
        }

        $this->info("Checked {$alerts->count()} alerts, {$triggered} triggered.");
        return self::SUCCESS;
    }
}
