<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\Turno;
use App\Services\TurnoAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnoAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_concesionario_returns_null_when_nobody_checked_in_today(): void
    {
        Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        $service = new TurnoAssignmentService();

        $this->assertNull($service->nextConcesionario());
    }

    public function test_check_in_creates_a_turno_for_today(): void
    {
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $service = new TurnoAssignmentService();

        $service->checkIn($a);

        $this->assertTrue(
            Turno::where('concesionario_id', $a->id)->whereDate('fecha', today())->exists()
        );
    }

    public function test_check_in_twice_same_day_does_not_duplicate(): void
    {
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $service = new TurnoAssignmentService();

        $service->checkIn($a);
        $service->checkIn($a);

        $this->assertEquals(1, Turno::where('concesionario_id', $a->id)->count());
    }

    public function test_next_concesionario_picks_whoever_arrived_first(): void
    {
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);
        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);

        $service = new TurnoAssignmentService();

        $this->assertTrue($service->nextConcesionario()->is($a));
    }

    public function test_rotation_cycles_through_arrived_concesionarios(): void
    {
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $c = Concesionario::create(['nombre' => 'C', 'peso_asignacion' => 1, 'activo' => true]);

        $service = new TurnoAssignmentService();

        // Llegan en orden A, B, C
        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(30)]);
        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(20)]);
        Turno::create(['concesionario_id' => $c->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);

        $orden = [];

        // Cada turno se confirma con al menos un segundo de diferencia real
        // (como pasaría en la práctica); si no, varias asignaciones caen en
        // el mismo segundo y el desempate por id rompe el ciclo.
        for ($i = 0; $i < 6; $i++) {
            $siguiente = $service->nextConcesionario();
            $orden[] = $siguiente->nombre;
            $service->registrarAsignacion($siguiente);
            \Illuminate\Support\Carbon::setTestNow(now()->addSecond());
        }

        \Illuminate\Support\Carbon::setTestNow();

        $this->assertEquals(['A', 'B', 'C', 'A', 'B', 'C'], $orden);
    }

    public function test_next_concesionario_ignores_inactive_concesionarios(): void
    {
        $inactivo = Concesionario::create(['nombre' => 'Inactivo', 'peso_asignacion' => 1, 'activo' => false]);
        $activo = Concesionario::create(['nombre' => 'Activo', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $inactivo->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(30)]);
        Turno::create(['concesionario_id' => $activo->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);

        $service = new TurnoAssignmentService();

        $this->assertTrue($service->nextConcesionario()->is($activo));
    }

    public function test_next_concesionario_ignores_turnos_from_other_days(): void
    {
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today()->subDay(), 'llegada_at' => now()->subDay()]);

        $service = new TurnoAssignmentService();

        $this->assertNull($service->nextConcesionario());
    }

    public function test_check_out_removes_todays_turno(): void
    {
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $service = new TurnoAssignmentService();
        $service->checkIn($a);

        $service->checkOut($a);

        $this->assertDatabaseMissing('turnos', ['concesionario_id' => $a->id]);
    }
}
