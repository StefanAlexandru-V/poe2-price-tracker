<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Alert $alert,
        public float $currentPrice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $item = $this->alert->item;

        return (new MailMessage)
            ->subject("Price Alert: {$item->name}")
            ->line("{$item->name} is now {$this->currentPrice} div.")
            ->line("Your alert: {$this->alert->operator} {$this->alert->threshold} div")
            ->action('View Price', url("/item/{$item->slug}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'item_name' => $this->alert->item->name,
            'price' => $this->currentPrice,
            'threshold' => $this->alert->threshold,
            'operator' => $this->alert->operator,
        ];
    }
}
