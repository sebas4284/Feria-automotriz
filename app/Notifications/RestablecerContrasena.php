<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestablecerContrasena extends Notification
{
    public function __construct(#[\SensitiveParameter] public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $minutos = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Restablece tu contraseña — AutoFeria CRM')
            ->greeting('Hola')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta en AutoFeria CRM.')
            ->action('Restablecer contraseña', $url)
            ->line("Este enlace vence en {$minutos} minutos.")
            ->line('Si tú no pediste este cambio, puedes ignorar este correo — tu contraseña sigue siendo la misma.');
    }
}
