<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnoRotacionTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $rol): User
    {
        return User::factory()->create(['rol' => $rol]);
    }

    public function test_confirmar_el_siguiente_registra_su_asignacion_y_se_queda_en_turnos(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        $this->actingAs($admin)->post('/turnos/rotar', ['concesionario_id' => $a->id])
            ->assertRedirect(route('turnos.index'));

        $this->assertDatabaseHas('turnos', ['concesionario_id' => $a->id, 'veces_asignado' => 1]);
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $b->id, 'veces_asignado' => 0]);
    }

    public function test_no_esta_pasa_el_turno_al_de_atras_sin_afectar_al_saltado(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        // A es quien sigue, pero no está: se confirma directamente a B (el de atrás).
        $this->actingAs($admin)->post('/turnos/rotar', ['concesionario_id' => $b->id])
            ->assertRedirect(route('turnos.index'));

        $this->assertDatabaseHas('turnos', ['concesionario_id' => $a->id, 'veces_asignado' => 0]);
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $b->id, 'veces_asignado' => 1]);
    }

    public function test_no_se_puede_confirmar_un_concesionario_que_no_esta_en_la_fila_de_hoy(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $sinLlegar = Concesionario::create(['nombre' => 'Sin Llegar', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()]);

        $this->actingAs($admin)->post('/turnos/rotar', ['concesionario_id' => $sinLlegar->id])
            ->assertRedirect(route('turnos.index'));

        $this->assertDatabaseMissing('turnos', ['concesionario_id' => $sinLlegar->id]);
    }

    public function test_fila_estricta_no_reordena_por_conteo_total_sino_por_ultima_vez_atendido(): void
    {
        // Reproduce el caso de la captura: A y B llegaron primero y ya
        // fueron atendidos una vez cada uno; C llegó después y todavía no.
        // La fila estricta debe seguir el orden real de espera (A fue
        // atendido hace más tiempo que B), no "todos los de 0 primero".
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $c = Concesionario::create(['nombre' => 'C', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create([
            'concesionario_id' => $a->id, 'fecha' => today(),
            'llegada_at' => now()->subMinutes(20), 'veces_asignado' => 1,
            'ultima_asignacion_at' => now()->subMinutes(15),
        ]);
        Turno::create([
            'concesionario_id' => $b->id, 'fecha' => today(),
            'llegada_at' => now()->subMinutes(18), 'veces_asignado' => 1,
            'ultima_asignacion_at' => now()->subMinutes(5),
        ]);
        Turno::create([
            'concesionario_id' => $c->id, 'fecha' => today(),
            'llegada_at' => now()->subMinutes(10),
        ]);

        $service = new \App\Services\TurnoAssignmentService();

        // C nunca ha sido atendido, pero llegó después de que A y B ya
        // hubieran sido atendidos por última vez hace 15 y 5 minutos
        // respectivamente — en fila estricta, C (llegó hace 10 min) va
        // antes que B (esperando desde hace solo 5 min), pero después de
        // A (esperando desde hace 15 min).
        $this->assertTrue($service->nextConcesionario()->is($a));
    }

    public function test_turnos_screen_shows_ronda_and_detras(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        $response = $this->actingAs($admin)->get('/turnos');

        $response->assertOk();
        $response->assertSee('Ronda 1');
        $response->assertSee('Detrás: B');
    }
}
