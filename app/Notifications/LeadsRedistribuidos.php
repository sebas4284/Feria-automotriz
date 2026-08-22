<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class LeadsRedistribuidos extends Notification
{
    public function __construct(private int $cantidad)
    {
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, self $notification): WebPushMessage
    {
        $texto = $this->cantidad === 1 ? '1 lead' : "{$this->cantidad} leads";

        return (new WebPushMessage())
            ->title('Leads redistribuidos')
            ->icon('/pwa-192x192.png')
            ->badge('/badge-96x96.png')
            ->body("Recibiste {$texto} en la redistribución de vencidos/sin asesor.")
            ->data(['url' => route('leads.redistribucion')]);
    }
}
