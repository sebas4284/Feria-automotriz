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

        for ($i = 0; $i < 50; $i++) {
            $target = $service->assignNext();
            $this->makeLead($target);
        }

        $totalA = Lead::where('concesionario_id', $a->id)->count();
        $totalB = Lead::where('concesionario_id', $b->id)->count();
        $totalC = Lead::where('concesionario_id', $c->id)->count();

        $this->assertEqualsWithDelta(30, $totalA, 3);
        $this->assertEqualsWithDelta(10, $totalB, 3);
        $this->assertEqualsWithDelta(10, $totalC, 3);
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
