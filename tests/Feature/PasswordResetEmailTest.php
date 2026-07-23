<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RestablecerContrasena;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_notification_mail_is_in_spanish_and_has_the_reset_url(): void
    {
        $user = User::factory()->create();

        $notification = new RestablecerContrasena('un-token-de-prueba');
        $mail = $notification->toMail($user);

        $this->assertSame('Restablece tu contraseña — AutoFeria CRM', $mail->subject);
        $this->assertStringContainsString(
            'Recibimos una solicitud para restablecer la contraseña',
            implode(' ', $mail->introLines)
        );
        $this->assertStringContainsString('Restablecer contraseña', $mail->actionText);
        $this->assertStringContainsString('reset-password/un-token-de-prueba', $mail->actionUrl);
        $this->assertStringContainsString(urlencode($user->email), $mail->actionUrl);
    }

    public function test_forgot_password_dispatches_the_spanish_notification(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        \Illuminate\Support\Facades\Notification::assertSentTo($user, RestablecerContrasena::class);
    }
}
