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

    public function test_confirmar_turno_registra_asignacion_y_redirige_a_nuevo_cliente(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        $this->actingAs($admin)->post('/turnos/rotar', ['concesionario_id' => $a->id])
            ->assertRedirect(route('clientes.create', ['concesionario_id' => $a->id, 'cita' => 0]));

        $this->assertDatabaseHas('turnos', ['concesionario_id' => $a->id, 'veces_asignado' => 1]);
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $b->id, 'veces_asignado' => 0]);
    }

    public function test_confirmar_turno_con_concesionario_equivocado_no_registra_nada(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        // A es quien realmente sigue (llegó primero); se intenta confirmar B por error.
        $this->actingAs($admin)->post('/turnos/rotar', ['concesionario_id' => $b->id])
            ->assertRedirect();

        $this->assertDatabaseHas('turnos', ['concesionario_id' => $a->id, 'veces_asignado' => 0]);
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $b->id, 'veces_asignado' => 0]);
    }

    public function test_cliente_create_view_muestra_concesionario_prefijado_y_lo_envia_oculto(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'Auto Sol', 'peso_asignacion' => 1, 'activo' => true]);

        $response = $this->actingAs($admin)->get(route('clientes.create', ['concesionario_id' => $a->id, 'cita' => 0]));

        $response->assertOk();
        $response->assertSee('Auto Sol');
        $response->assertSee('name="concesionario_id" value="' . $a->id . '"', false);
    }

    public function test_guardar_cliente_con_concesionario_prefijado_no_vuelve_a_registrar_asignacion(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10), 'veces_asignado' => 1]);

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Cliente Confirmado',
            'cita' => '0',
            'concesionario_id' => $a->id,
        ])->assertRedirect(route('clientes.index'));

        $cliente = Cliente::where('nombre', 'Cliente Confirmado')->first();
        $this->assertEquals($a->id, $cliente->concesionario_id);

        // El contador no debe subir de nuevo: ya se había incrementado al confirmar en /turnos.
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $a->id, 'veces_asignado' => 1]);
    }
}
