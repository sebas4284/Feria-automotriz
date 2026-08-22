<?php

namespace Tests\Feature;

use App\Http\Controllers\LeadController;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\LeadReassignment;
use App\Models\User;
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

        $this->actingAs($vfUser)->get(route('leads.redistribucion'))
            ->assertOk()
            ->assertSee($lead->full_name ?: $lead->meta_lead_id);
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
