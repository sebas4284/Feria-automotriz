<?php

namespace Tests\Feature;

use App\Models\Cliente;
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

    public function test_turnos_screen_shows_sugerido_badge_en_el_concesionario_correcto(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        $response = $this->actingAs($admin)->get('/turnos');

        $response->assertOk();
        $response->assertSee('Sugerido');
    }

    public function test_concesionario_asignado_pasa_al_final_de_la_fila_visible(): void
    {
        $admin = $this->makeUser('admin');
        $uno = Concesionario::create(['nombre' => 'Concesionario Uno', 'peso_asignacion' => 1, 'activo' => true]);
        $dos = Concesionario::create(['nombre' => 'Concesionario Dos', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $uno->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $dos->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        // Antes de asignar, Uno (llegó primero) va antes que Dos en la tabla.
        $antes = $this->actingAs($admin)->get('/turnos');
        $antes->assertSeeInOrder(['Concesionario Uno', 'Concesionario Dos']);

        $cliente = Cliente::create(['nombre' => 'Pendiente Uno', 'cita' => false, 'concesionario_id' => null]);

        $this->actingAs($admin)->postJson('/turnos/asignar-cliente', [
            'cliente_id' => $cliente->id,
            'concesionario_id' => $uno->id,
        ])->assertOk();

        // Tras asignarle un cliente, Uno pasa al final: ahora Dos aparece primero.
        $despues = $this->actingAs($admin)->get('/turnos');
        $despues->assertOk();
        $despues->assertSeeInOrder(['Concesionario Dos', 'Concesionario Uno']);
    }

    public function test_numero_de_llegada_es_fijo_y_no_cambia_cuando_rota_el_turno(): void
    {
        $admin = $this->makeUser('admin');
        $uno = Concesionario::create(['nombre' => 'Concesionario Uno', 'peso_asignacion' => 1, 'activo' => true]);
        $dos = Concesionario::create(['nombre' => 'Concesionario Dos', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $uno->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $dos->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        // Uno llegó primero: Llegada #1 y Turno #1.
        $antes = $this->actingAs($admin)->get('/turnos');
        $antes->assertSeeInOrder(['Concesionario Uno', 'Llegada #1', 'Turno #1']);
        $antes->assertSeeInOrder(['Concesionario Dos', 'Llegada #2', 'Turno #2']);

        $cliente = Cliente::create(['nombre' => 'Pendiente Uno', 'cita' => false, 'concesionario_id' => null]);

        $this->actingAs($admin)->postJson('/turnos/asignar-cliente', [
            'cliente_id' => $cliente->id,
            'concesionario_id' => $uno->id,
        ])->assertOk();

        // Tras atenderlo, el Turno # de Uno rota al final, pero su Llegada # sigue siendo #1.
        $despues = $this->actingAs($admin)->get('/turnos');
        $despues->assertSeeInOrder(['Concesionario Uno', 'Llegada #1', 'Turno #2']);
        $despues->assertSeeInOrder(['Concesionario Dos', 'Llegada #2', 'Turno #1']);
    }

    public function test_pantalla_muestra_mensaje_de_espera_sin_clientes_hoy(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->get('/turnos/pantalla');

        $response->assertOk();
        $response->assertSee('Esperando el primer cliente del día');
    }

    public function test_pantalla_muestra_las_ultimas_asignaciones_y_su_concesionario(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'Auto Sol', 'peso_asignacion' => 1, 'activo' => true]);

        \App\Models\Cliente::create([
            'nombre' => 'Cliente Prueba',
            'cita' => false,
            'concesionario_id' => $conc->id,
        ]);

        $response = $this->actingAs($admin)->get('/turnos/pantalla');

        $response->assertOk();
        $response->assertSee('Cliente Prueba');
        $response->assertSee('Auto Sol');
    }

    public function test_asignar_cliente_arrastrado_lo_asigna_y_avanza_la_cola(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        Turno::create(['concesionario_id' => $conc->id, 'fecha' => today(), 'llegada_at' => now()]);

        $cliente = Cliente::create(['nombre' => 'Pendiente Uno', 'cita' => false, 'concesionario_id' => null]);

        $this->actingAs($admin)->postJson('/turnos/asignar-cliente', [
            'cliente_id' => $cliente->id,
            'concesionario_id' => $conc->id,
        ])->assertOk();

        $this->assertSame($conc->id, $cliente->fresh()->concesionario_id);
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $conc->id, 'veces_asignado' => 1]);
    }

    public function test_no_se_puede_asignar_a_un_concesionario_que_no_esta_en_la_fila(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'Sin Llegar', 'peso_asignacion' => 1, 'activo' => true]);
        $cliente = Cliente::create(['nombre' => 'Pendiente Uno', 'cita' => false, 'concesionario_id' => null]);

        $this->actingAs($admin)->postJson('/turnos/asignar-cliente', [
            'cliente_id' => $cliente->id,
            'concesionario_id' => $conc->id,
        ])->assertStatus(422);

        $this->assertNull($cliente->fresh()->concesionario_id);
    }

    public function test_pendientes_se_muestran_en_turnos_index(): void
    {
        $admin = $this->makeUser('admin');
        Cliente::create(['nombre' => 'Pendiente Visible', 'cita' => false, 'concesionario_id' => null]);

        $response = $this->actingAs($admin)->get('/turnos');

        $response->assertOk();
        $response->assertSee('Pendiente Visible');
        $response->assertSee('Pendientes por asignar');
    }

    public function test_saltar_turno_manda_al_final_sin_contar_como_asignacion_real(): void
    {
        $admin = $this->makeUser('admin');
        $uno = Concesionario::create(['nombre' => 'Concesionario Uno', 'peso_asignacion' => 1, 'activo' => true]);
        $dos = Concesionario::create(['nombre' => 'Concesionario Dos', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $uno->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $dos->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        // Antes de saltar, Uno (llegó primero) va antes que Dos.
        $this->actingAs($admin)->get('/turnos')->assertSeeInOrder(['Concesionario Uno', 'Concesionario Dos']);

        $this->actingAs($admin)->post("/turnos/{$uno->id}/saltar")->assertRedirect();

        // Tras saltarlo, Uno pasa al final: ahora Dos aparece primero.
        $this->actingAs($admin)->get('/turnos')->assertSeeInOrder(['Concesionario Dos', 'Concesionario Uno']);

        $this->assertDatabaseHas('turnos', [
            'concesionario_id' => $uno->id,
            'veces_asignado' => 0,
            'veces_procesado' => 1,
        ]);
    }

    public function test_ronda_actual_sube_cuando_todos_los_de_la_fila_han_sido_procesados(): void
    {
        $admin = $this->makeUser('admin');
        $uno = Concesionario::create(['nombre' => 'Concesionario Uno', 'peso_asignacion' => 1, 'activo' => true]);
        $dos = Concesionario::create(['nombre' => 'Concesionario Dos', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $uno->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $dos->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        $service = new \App\Services\TurnoAssignmentService();

        $this->assertSame(1, $service->rondaActual());

        // Solo Uno ha sido procesado (saltado): Dos todavía no completa su primera vuelta.
        $service->enviarAlFinal($uno);
        $this->assertSame(1, $service->rondaActual());

        // Ahora los dos han sido procesados una vez cada uno: arranca la ronda 2.
        $service->enviarAlFinal($dos);
        $this->assertSame(2, $service->rondaActual());
    }

    public function test_pantalla_muestra_en_turno_y_se_prepara(): void
    {
        $admin = $this->makeUser('admin');
        $uno = Concesionario::create(['nombre' => 'Concesionario Uno', 'peso_asignacion' => 1, 'activo' => true]);
        $dos = Concesionario::create(['nombre' => 'Concesionario Dos', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $uno->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $dos->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        $response = $this->actingAs($admin)->get('/turnos/pantalla');

        $response->assertOk();
        $response->assertSee('En turno');
        $response->assertSee('Se prepara');
        $response->assertSeeInOrder(['Concesionario Uno', 'Concesionario Dos']);
    }
}
