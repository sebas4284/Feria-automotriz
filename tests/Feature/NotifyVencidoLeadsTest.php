<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadVencido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyVencidoLeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_admins_for_leads_that_just_became_vencido(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['rol' => 'admin']);
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $lead = Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Asignado',
            'concesionario_id' => $conc->id,
            'assigned_at' => now()->subHours(25),
        ]);

        $this->artisan('leads:notify-vencidos')->assertSuccessful();

        Notification::assertSentTo($admin, LeadVencido::class);
        $this->assertNotNull($lead->fresh()->vencido_notified_at);
    }

    public function test_does_not_notify_twice_for_the_same_vencido_lead(): void
    {
        Notification::fake();

        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Asignado',
            'concesionario_id' => $conc->id,
            'assigned_at' => now()->subHours(25),
            'vencido_notified_at' => now()->subHours(1),
        ]);

        $this->artisan('leads:notify-vencidos')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_does_not_notify_leads_that_are_not_vencido(): void
    {
        Notification::fake();

        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Asignado',
            'concesionario_id' => $conc->id,
            'assigned_at' => now()->subHours(1),
        ]);

        $this->artisan('leads:notify-vencidos')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
