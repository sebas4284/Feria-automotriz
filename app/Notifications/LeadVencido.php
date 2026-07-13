<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class LeadVencido extends Notification
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
        $responsable = $this->lead->asesorComercial->nombre
            ?? $this->lead->concesionario->nombre
            ?? 'sin asignar';

        return (new WebPushMessage())
            ->title('Lead vencido')
            ->icon('/pwa-192x192.png')
            ->badge('/badge-96x96.png')
            ->body("{$nombre} lleva " . config('leads.staleness_hours') . "h sin avanzar ({$responsable})")
            ->data(['url' => route('leads.show', $this->lead)]);
    }
}
