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

        // Selección aleatoria ponderada: con 200 muestras, un margen de ±25 cubre
        // ampliamente la varianza esperada (~3 desviaciones estándar) sin ser frágil.
        $this->assertEqualsWithDelta(120, $totalA, 25);
        $this->assertEqualsWithDelta(40, $totalB, 20);
        $this->assertEqualsWithDelta(40, $totalC, 20);
    }

    public function test_new_concesionario_receives_leads_immediately_without_excluding_the_old_one(): void
    {
        $viejo = Concesionario::create(['nombre' => 'Viejo', 'peso_asignacion' => 10, 'activo' => true]);
        $nuevo = Concesionario::create(['nombre' => 'Nuevo', 'peso_asignacion' => 5, 'activo' => true]);

        // Histórico grande del concesionario viejo: no debe impedir que siga recibiendo leads.
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
