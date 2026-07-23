<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeLead(?Concesionario $concesionario = null): Lead
    {
        return Lead::create([
            'meta_lead_id' => 'l:'.uniqid(),
            'estado_gestion' => 'Nuevo',
            'concesionario_id' => $concesionario?->id,
            'assigned_at' => $concesionario ? now() : null,
        ]);
    }

    public function test_assign_next_distributes_leads_proportionally_to_weight(): void
    {
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 3, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $c = Concesionario::create(['nombre' => 'C', 'peso_asignacion' => 1, 'activo' => true]);

        $service = new LeadAssignmentService();

        for ($i = 0; $i < 200; $i++) {
            $target = $service->assignNext();
            $this->makeLead($target);
        }

        $totalA = Lead::where('concesionario_id', $a->id)->count();
        $totalB = Lead::where('concesionario_id', $b->id)->count();
        $totalC = Lead::where('concesionario_id', $c->id)->count();

        // Smooth weighted round-robin converge exacto: 200 es múltiplo del peso
        // total (5), así que las proporciones 3:1:1 se cumplen sin margen.
        $this->assertSame(120, $totalA);
        $this->assertSame(40, $totalB);
        $this->assertSame(40, $totalC);
    }

    public function test_new_concesionario_receives_leads_immediately_without_excluding_the_old_one(): void
    {
        $viejo = Concesionario::create(['nombre' => 'Viejo', 'peso_asignacion' => 10, 'activo' => true]);
        $nuevo = Concesionario::create(['nombre' => 'Nuevo', 'peso_asignacion' => 5, 'activo' => true]);

        // Histórico grande del concesionario viejo: con SWRR el estado del algoritmo
        // vive en swrr_current_weight, no en el conteo de leads, así que este
        // histórico no debe influir en nada; solo confirma que ambos siguen recibiendo.
        for ($i = 0; $i < 150; $i++) {
            $this->makeLead($viejo);
        }

        $service = new LeadAssignmentService();

        for ($i = 0; $i < 20; $i++) {
            $target = $service->assignNext();
            $this->makeLead($target);
        }

        $nuevosViejo = Lead::where('concesionario_id', $viejo->id)->count() - 150;
        $nuevosNuevo = Lead::where('concesionario_id', $nuevo->id)->count();

        // En una sola tanda de 20, ambos deben recibir leads (nadie acapara el 100%).
        $this->assertGreaterThan(0, $nuevosViejo);
        $this->assertGreaterThan(0, $nuevosNuevo);
        $this->assertSame(20, $nuevosViejo + $nuevosNuevo);
    }

    public function test_assign_next_ignores_inactive_concesionarios(): void
    {
        Concesionario::create(['nombre' => 'Inactivo', 'peso_asignacion' => 5, 'activo' => false]);
        $activo = Concesionario::create(['nombre' => 'Activo', 'peso_asignacion' => 1, 'activo' => true]);

        $service = new LeadAssignmentService();

        $this->assertTrue($service->assignNext()->is($activo));
    }

    public function test_assign_next_returns_null_when_no_active_concesionario(): void
    {
        Concesionario::create(['nombre' => 'Inactivo', 'peso_asignacion' => 1, 'activo' => false]);

        $service = new LeadAssignmentService();

        $this->assertNull($service->assignNext());
    }

    public function test_reassign_updates_lead_resets_assigned_at_and_logs_audit_row(): void
    {
        $from = Concesionario::create(['nombre' => 'Origen', 'peso_asignacion' => 1, 'activo' => true]);
        $to = Concesionario::create(['nombre' => 'Destino', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = User::factory()->create();

        $lead = $this->makeLead($from);
        $lead->update(['assigned_at' => now()->subHours(49)]);

        $service = new LeadAssignmentService();
        $service->reassign($lead->fresh(), $to, $admin, 'no respondió a tiempo');

        $lead->refresh();

        $this->assertEquals($to->id, $lead->concesionario_id);
        $this->assertTrue($lead->assigned_at->gt(now()->subMinute()));
        $this->assertEquals('Nuevo', $lead->estado_gestion);

        $this->assertDatabaseHas('lead_reassignments', [
            'lead_id' => $lead->id,
            'from_concesionario_id' => $from->id,
            'to_concesionario_id' => $to->id,
            'reassigned_by' => $admin->id,
            'motivo' => 'no respondió a tiempo',
        ]);
    }
}
