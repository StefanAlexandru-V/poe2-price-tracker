<?php

namespace App\Console\Commands;

use App\Models\Alert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAlerts extends Command
{
    protected $signature = 'alerts:check';
    protected $description = 'Check active alerts against latest prices and notify users';

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

            // TODO: send notification (email/database) instead of just logging
            Log::info("[Alert] Triggered for {$alert->user->name}: {$alert->item->name} is {$price} div ({$alert->operator} {$alert->threshold})");

            $alert->update(['last_triggered_at' => now()]);
            $triggered++;
        }

        $this->info("Checked {$alerts->count()} alerts, {$triggered} triggered.");
        return self::SUCCESS;
    }
}
