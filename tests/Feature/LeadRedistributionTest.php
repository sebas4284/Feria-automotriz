<?php

namespace Tests\Feature;

use App\Http\Controllers\LeadController;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\LeadReassignment;
use App\Models\User;
use App\Notifications\LeadsRedistribuidos;
use App\Notifications\NuevoLeadAsignado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeadRedistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_redistributes_leads_that_are_either_stale_or_unassigned_evenly_among_target_concesionarios(): void
    {
        Notification::fake();

        [$vfMotors, $auto2, $puntokar] = $this->createTargetConcesionarios();
        $otro = Concesionario::create(['nombre' => 'Otro Concesionario', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = User::factory()->create(['rol' => 'admin']);
        $asesor = \App\Models\AsesorComercial::create(['cedula' => '999', 'nombre' => 'Asesor Existente', 'concesionario_id' => $otro->id]);

        // Vencidos y sin asesor a la vez: deben moverse.
        $ambos = [];
        for ($i = 0; $i < 6; $i++) {
            $ambos[] = Lead::create([
                'meta_lead_id' => "ambos-{$i}",
                'estado_gestion' => 'Nuevo',
                'concesionario_id' => $otro->id,
                'asesor_comercial_id' => null,
                'assigned_at' => now()->subHours(200 - ($i * 10)),
            ]);
        }

        // Vencido pero con asesor ya asignado: también debe moverse (basta una de las dos condiciones).
        $vencidoConAsesor = Lead::create([
            'meta_lead_id' => 'vencido-con-asesor',
            'estado_gestion' => 'Asignado',
            'concesionario_id' => $otro->id,
            'asesor_comercial_id' => $asesor->id,
            'assigned_at' => now()->subHours(140),
        ]);

        // Sin asesor pero recién asignado (no vencido todavía): también debe moverse.
        $recienteSinAsesor = Lead::create([
            'meta_lead_id' => 'reciente-sin-asesor',
            'estado_gestion' => 'Nuevo',
            'concesionario_id' => $otro->id,
            'asesor_comercial_id' => null,
            'assigned_at' => now()->subHour(),
        ]);

        // Ni vencido ni sin asesor: no debe tocarse.
        $sano = Lead::create([
            'meta_lead_id' => 'sano',
            'estado_gestion' => 'Contactado',
            'concesionario_id' => $otro->id,
            'asesor_comercial_id' => $asesor->id,
            'assigned_at' => now()->subHour(),
        ]);

        $this->actingAs($admin)->post(route('leads.redistribucion.ejecutar'));

        $candidatos = array_merge($ambos, [$vencidoConAsesor, $recienteSinAsesor]);
        $esperado = [$vfMotors->id, $auto2->id, $puntokar->id, $vfMotors->id, $auto2->id, $puntokar->id, $vfMotors->id, $auto2->id];
        foreach ($candidatos as $i => $candidato) {
            $this->assertSame($esperado[$i], $candidato->fresh()->concesionario_id, "candidato {$i}");
            $this->assertNull($candidato->fresh()->asesor_comercial_id, "candidato {$i} debe quedar sin asesor");
        }

        $this->assertSame($otro->id, $sano->fresh()->concesionario_id);
        $this->assertSame($asesor->id, $sano->fresh()->asesor_comercial_id);

        $this->assertSame(8, LeadReassignment::where('motivo', LeadController::REDISTRIBUCION_MOTIVO)->count());

        // Una sola notificación consolidada por concesionario destino, no una por lead
        // (con 8 leads repartidos esto evita mandar 8+ notificaciones sincrónicas por request).
        Notification::assertSentTimes(LeadsRedistribuidos::class, 3);
        Notification::assertSentTimes(NuevoLeadAsignado::class, 0);
    }

    public function test_redistribution_fails_loudly_and_moves_nothing_when_a_target_concesionario_is_missing_or_inactive(): void
    {
        Notification::fake();

        $vfMotors = Concesionario::create(['nombre' => 'VF Motors', 'peso_asignacion' => 1, 'activo' => true]);
        $auto2 = Concesionario::create(['nombre' => 'Auto 2 SAS', 'peso_asignacion' => 1, 'activo' => true]);
        // "Puntokar multimarcas SAS" quedó inactivo (p.ej. por un desajuste al sincronizar el sheet).
        Concesionario::create(['nombre' => 'Puntokar multimarcas SAS', 'peso_asignacion' => 1, 'activo' => false]);
        $otro = Concesionario::create(['nombre' => 'Otro Concesionario', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = User::factory()->create(['rol' => 'admin']);

        $candidato = Lead::create([
            'meta_lead_id' => 'candidato',
            'estado_gestion' => 'Nuevo',
            'concesionario_id' => $otro->id,
            'asesor_comercial_id' => null,
            'assigned_at' => now()->subHours(100),
        ]);

        $response = $this->actingAs($admin)->post(route('leads.redistribucion.ejecutar'));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Puntokar multimarcas SAS', session('error'));
        $this->assertSame(0, LeadReassignment::count());
        $this->assertSame($otro->id, $candidato->fresh()->concesionario_id);
        Notification::assertNothingSent();
    }

    public function test_admin_sees_the_redistribution_screen_with_the_resolved_targets_preview(): void
    {
        $this->createTargetConcesionarios();
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->get(route('leads.redistribucion'))
            ->assertOk()
            ->assertSee('Se repartirá entre')
            ->assertSee('VF Motors')
            ->assertSee('Auto 2 SAS')
            ->assertSee('Puntokar multimarcas SAS');
    }

    public function test_concesionario_user_cannot_execute_redistribution(): void
    {
        $this->createTargetConcesionarios();
        $concUser = User::factory()->create(['rol' => 'concesionario']);

        $this->actingAs($concUser)->post(route('leads.redistribucion.ejecutar'))->assertForbidden();
    }

    public function test_concesionario_user_only_sees_leads_received_by_their_own_concesionario(): void
    {
        [$vfMotors, $auto2] = $this->createTargetConcesionarios();
        $admin = User::factory()->create(['rol' => 'admin']);
        $vfUser = User::factory()->create(['rol' => 'concesionario', 'concesionario_id' => $vfMotors->id]);

        $lead = Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Nuevo',
            'concesionario_id' => $auto2->id,
            'assigned_at' => now()->subHours(100),
        ]);

        \App\Models\LeadReassignment::create([
            'lead_id' => $lead->id,
            'from_concesionario_id' => $auto2->id,
            'to_concesionario_id' => $vfMotors->id,
            'reassigned_by' => $admin->id,
            'motivo' => LeadController::REDISTRIBUCION_MOTIVO,
        ]);

        $leadDeAuto2 = Lead::create([
            'meta_lead_id' => 'l2-para-auto2',
            'full_name' => 'Lead Para Auto2',
            'estado_gestion' => 'Nuevo',
            'concesionario_id' => $auto2->id,
            'assigned_at' => now()->subHours(100),
        ]);

        \App\Models\LeadReassignment::create([
            'lead_id' => $leadDeAuto2->id,
            'from_concesionario_id' => $vfMotors->id,
            'to_concesionario_id' => $auto2->id,
            'reassigned_by' => $admin->id,
            'motivo' => LeadController::REDISTRIBUCION_MOTIVO,
        ]);

        $this->actingAs($vfUser)->get(route('leads.redistribucion'))
            ->assertOk()
            ->assertSee($lead->full_name ?: $lead->meta_lead_id)
            ->assertDontSee('Lead Para Auto2');
    }

    private function createTargetConcesionarios(): array
    {
        return [
            Concesionario::create(['nombre' => 'VF Motors', 'peso_asignacion' => 1, 'activo' => true]),
            Concesionario::create(['nombre' => 'Auto 2 SAS', 'peso_asignacion' => 1, 'activo' => true]),
            Concesionario::create(['nombre' => 'Puntokar multimarcas SAS', 'peso_asignacion' => 1, 'activo' => true]),
        ];
    }
}
