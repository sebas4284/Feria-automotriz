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

    public function test_admin_redistributes_stale_unassigned_leads_evenly_among_target_concesionarios(): void
    {
        Notification::fake();

        [$vfMotors, $auto2, $puntokar] = $this->createTargetConcesionarios();
        $otro = Concesionario::create(['nombre' => 'Otro Concesionario', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = User::factory()->create(['rol' => 'admin']);

        $candidatos = [];
        for ($i = 0; $i < 6; $i++) {
            $candidatos[] = Lead::create([
                'meta_lead_id' => "candidato-{$i}",
                'estado_gestion' => 'Nuevo',
                'concesionario_id' => $otro->id,
                'asesor_comercial_id' => null,
                'assigned_at' => now()->subHours(150 - $i),
            ]);
        }

        $conAsesor = Lead::create([
            'meta_lead_id' => 'con-asesor',
            'estado_gestion' => 'Asignado',
            'concesionario_id' => $otro->id,
            'asesor_comercial_id' => null,
            'assigned_at' => now()->subHours(200),
        ]);
        \App\Models\AsesorComercial::create(['cedula' => '999', 'nombre' => 'Asesor Existente', 'concesionario_id' => $otro->id]);
        $conAsesor->update(['asesor_comercial_id' => \App\Models\AsesorComercial::first()->id]);

        $reciente = Lead::create([
            'meta_lead_id' => 'reciente',
            'estado_gestion' => 'Nuevo',
            'concesionario_id' => $otro->id,
            'asesor_comercial_id' => null,
            'assigned_at' => now()->subHour(),
        ]);

        $this->actingAs($admin)->post(route('leads.redistribucion.ejecutar'));

        $esperado = [$vfMotors->id, $auto2->id, $puntokar->id, $vfMotors->id, $auto2->id, $puntokar->id];
        foreach ($candidatos as $i => $candidato) {
            $this->assertSame($esperado[$i], $candidato->fresh()->concesionario_id, "candidato {$i}");
            $this->assertNull($candidato->fresh()->asesor_comercial_id);
        }

        $this->assertSame($otro->id, $conAsesor->fresh()->concesionario_id);
        $this->assertSame($otro->id, $reciente->fresh()->concesionario_id);

        $this->assertSame(6, LeadReassignment::where('motivo', LeadController::REDISTRIBUCION_MOTIVO)->count());
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
