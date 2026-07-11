<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NuevoLeadAsignado extends Notification
{
    public function __construct(private Lead $lead)
    {
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, self $notification): WebPushMessage
    {
        $nombre = $this->lead->full_name ?: 'Sin nombre';
        $body = $nombre . ($this->lead->phone_number ? ' · ' . $this->lead->phone_number : '');

        return (new WebPushMessage())
            ->title('Nuevo lead asignado')
            ->icon('/pwa-192x192.png')
            ->badge('/badge-96x96.png')
            ->body($body)
            ->data(['url' => route('leads.show', $this->lead)]);
    }
}
