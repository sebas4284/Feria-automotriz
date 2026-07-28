<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PorteriaTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $rol): User
    {
        return User::factory()->create(['rol' => $rol]);
    }

    private function makeVehiculo(array $overrides = []): Vehiculo
    {
        return Vehiculo::create(array_merge([
            'placa' => 'ABC' . random_int(100, 999),
            'marca' => 'Marca',
            'linea' => 'Linea',
            'modelo' => 2024,
            'estado' => 'Disponible',
            'ubicacion' => 'Dentro del área',
        ], $overrides));
    }

    public function test_porteria_puede_ver_su_pantalla(): void
    {
        $porteria = $this->makeUser('porteria');

        $this->actingAs($porteria)->get('/porteria')->assertOk();
    }

    public function test_porteria_no_puede_acceder_a_nada_mas(): void
    {
        $porteria = $this->makeUser('porteria');

        $this->actingAs($porteria)->get('/clientes')->assertForbidden();
        $this->actingAs($porteria)->get('/ventas')->assertForbidden();
        $this->actingAs($porteria)->get('/leads')->assertForbidden();
        $this->actingAs($porteria)->get('/vehiculos')->assertForbidden();
        $this->actingAs($porteria)->get('/turnos')->assertForbidden();
        $this->actingAs($porteria)->get('/dashboard')->assertForbidden();
    }

    public function test_concesionario_no_puede_acceder_a_porteria(): void
    {
        $concesionario = $this->makeUser('concesionario');

        $this->actingAs($concesionario)->get('/porteria')->assertForbidden();
    }

    public function test_porteria_puede_marcar_ingreso_de_vehiculo_dentro_del_area(): void
    {
        $porteria = $this->makeUser('porteria');
        $vehiculo = $this->makeVehiculo(['placa' => 'ABC123', 'ubicacion' => 'Dentro del área']);

        $this->actingAs($porteria)->post("/porteria/{$vehiculo->id}/ingreso")
            ->assertRedirect();

        $this->assertNotNull($vehiculo->fresh()->ingresado_at);
    }

    public function test_no_se_puede_marcar_ingreso_de_vehiculo_fuera_del_area(): void
    {
        $porteria = $this->makeUser('porteria');
        $vehiculo = $this->makeVehiculo(['placa' => 'XYZ789', 'ubicacion' => 'Fuera del área']);

        $this->actingAs($porteria)->post("/porteria/{$vehiculo->id}/ingreso")
            ->assertRedirect();

        $this->assertNull($vehiculo->fresh()->ingresado_at);
    }

    public function test_porteria_puede_deshacer_un_ingreso(): void
    {
        $porteria = $this->makeUser('porteria');
        $vehiculo = $this->makeVehiculo(['placa' => 'ABC124', 'ingresado_at' => now()]);

        $this->actingAs($porteria)->delete("/porteria/{$vehiculo->id}/ingreso")
            ->assertRedirect();

        $this->assertNull($vehiculo->fresh()->ingresado_at);
    }

    public function test_contador_de_ingresados_solo_cuenta_dentro_del_area(): void
    {
        $admin = $this->makeUser('admin');

        $this->makeVehiculo(['placa' => 'CNT001', 'ubicacion' => 'Dentro del área', 'ingresado_at' => now()]);
        $this->makeVehiculo(['placa' => 'CNT002', 'ubicacion' => 'Dentro del área']);
        $this->makeVehiculo(['placa' => 'CNT003', 'ubicacion' => 'Fuera del área', 'ingresado_at' => now()]);

        $response = $this->actingAs($admin)->get('/porteria');

        $response->assertOk();
        $response->assertSee('1 / 2');
    }
}
