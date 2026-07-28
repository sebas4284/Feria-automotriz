<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiculoCupoFeriaTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'placa' => 'CUP' . uniqid(),
            'marca' => 'Marca',
            'linea' => 'Linea',
            'modelo' => 2024,
            'estado' => 'Disponible',
            'ubicacion' => 'Dentro del área',
        ], $overrides);
    }

    public function test_creating_vehiculo_dentro_del_area_when_cupo_lleno_lo_guarda_fuera_del_area(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 2]);
        $admin = User::factory()->create(['rol' => 'admin']);

        Vehiculo::create($this->payload(['placa' => 'CUP001', 'concesionario_id' => $conc->id]));
        Vehiculo::create($this->payload(['placa' => 'CUP002', 'concesionario_id' => $conc->id]));

        $this->actingAs($admin)->post('/vehiculos', $this->payload([
            'placa' => 'CUP003',
            'concesionario_id' => $conc->id,
        ]))->assertRedirect(route('vehiculos.index'))
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('vehiculos', ['placa' => 'CUP003', 'ubicacion' => 'Fuera del área']);
    }

    public function test_can_create_vehiculo_fuera_del_area_without_limit(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 1]);
        $admin = User::factory()->create(['rol' => 'admin']);

        Vehiculo::create($this->payload(['placa' => 'CUP010', 'concesionario_id' => $conc->id]));

        $this->actingAs($admin)->post('/vehiculos', $this->payload([
            'placa' => 'CUP011',
            'concesionario_id' => $conc->id,
            'ubicacion' => 'Fuera del área',
        ]))->assertRedirect(route('vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['placa' => 'CUP011', 'ubicacion' => 'Fuera del área']);
    }

    public function test_marking_vehiculo_as_vendido_frees_up_its_cupo(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 1]);
        $admin = User::factory()->create(['rol' => 'admin']);

        $vendido = Vehiculo::create($this->payload(['placa' => 'CUP020', 'concesionario_id' => $conc->id]));

        // Al tope: un segundo vehículo "Dentro del área" se guarda como "Fuera del área".
        $this->actingAs($admin)->post('/vehiculos', $this->payload([
            'placa' => 'CUP021',
            'concesionario_id' => $conc->id,
        ]))->assertRedirect(route('vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['placa' => 'CUP021', 'ubicacion' => 'Fuera del área']);

        Vehiculo::where('placa', 'CUP021')->delete();

        // Se vende el primero: libera el cupo.
        $vendido->update(['estado' => 'Vendido']);

        $this->actingAs($admin)->post('/vehiculos', $this->payload([
            'placa' => 'CUP021',
            'concesionario_id' => $conc->id,
        ]))->assertRedirect(route('vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['placa' => 'CUP021', 'ubicacion' => 'Dentro del área']);
    }

    public function test_editing_a_vehiculo_already_dentro_del_area_without_changes_does_not_fail(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 1]);
        $admin = User::factory()->create(['rol' => 'admin']);

        $vehiculo = Vehiculo::create($this->payload(['placa' => 'CUP030', 'concesionario_id' => $conc->id]));

        $this->actingAs($admin)->put("/vehiculos/{$vehiculo->id}", $this->payload([
            'placa' => 'CUP030',
            'concesionario_id' => $conc->id,
        ]))->assertRedirect(route('vehiculos.show', $vehiculo));

        $this->assertEquals('Dentro del área', $vehiculo->fresh()->ubicacion);
    }

    public function test_no_limit_when_concesionario_has_no_cupo_configured(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => null]);
        $admin = User::factory()->create(['rol' => 'admin']);

        for ($i = 0; $i < 5; $i++) {
            Vehiculo::create($this->payload(['placa' => 'CUP04' . $i, 'concesionario_id' => $conc->id]));
        }

        $this->actingAs($admin)->post('/vehiculos', $this->payload([
            'placa' => 'CUP050',
            'concesionario_id' => $conc->id,
        ]))->assertRedirect(route('vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['placa' => 'CUP050']);
    }

    public function test_global_cupo_total_blocks_creation_even_if_individual_concesionario_has_room(): void
    {
        config(['feria.cupo_total' => 2]);

        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 10]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 10]);
        $admin = User::factory()->create(['rol' => 'admin']);

        Vehiculo::create($this->payload(['placa' => 'CUP060', 'concesionario_id' => $concA->id]));
        Vehiculo::create($this->payload(['placa' => 'CUP061', 'concesionario_id' => $concA->id]));

        $this->actingAs($admin)->post('/vehiculos', $this->payload([
            'placa' => 'CUP062',
            'concesionario_id' => $concB->id,
        ]))->assertRedirect(route('vehiculos.index'))
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('vehiculos', ['placa' => 'CUP062', 'ubicacion' => 'Fuera del área']);
    }

    public function test_reconciliar_cupo_demotes_newest_excess_vehiculos_dentro_del_area_when_over_cupo(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 2]);

        $v1 = Vehiculo::create($this->payload(['placa' => 'DEM001', 'concesionario_id' => $conc->id]));
        $v2 = Vehiculo::create($this->payload(['placa' => 'DEM002', 'concesionario_id' => $conc->id]));
        $v3 = Vehiculo::create($this->payload(['placa' => 'DEM003', 'concesionario_id' => $conc->id]));

        Vehiculo::where('placa', 'DEM001')->update(['created_at' => now()->subMinutes(3)]);
        Vehiculo::where('placa', 'DEM002')->update(['created_at' => now()->subMinutes(2)]);
        Vehiculo::where('placa', 'DEM003')->update(['created_at' => now()->subMinute()]);

        $this->artisan('vehiculos:reconciliar-cupo')->assertExitCode(0);

        // Cupo=2, había 3 dentro: el más reciente (DEM003) es el que sale.
        $this->assertEquals('Dentro del área', $v1->fresh()->ubicacion);
        $this->assertEquals('Dentro del área', $v2->fresh()->ubicacion);
        $this->assertEquals('Fuera del área', $v3->fresh()->ubicacion);
    }

    public function test_reconciliar_cupo_does_not_demote_vehiculos_already_ingresados(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 1]);

        $v1 = Vehiculo::create($this->payload(['placa' => 'DEM010', 'concesionario_id' => $conc->id, 'ingresado_at' => now()]));
        $v2 = Vehiculo::create($this->payload(['placa' => 'DEM011', 'concesionario_id' => $conc->id, 'ingresado_at' => now()]));
        $v3 = Vehiculo::create($this->payload(['placa' => 'DEM012', 'concesionario_id' => $conc->id]));

        $this->artisan('vehiculos:reconciliar-cupo')
            ->expectsOutputToContain('sigue 1 vehículo(s) por encima de su cupo')
            ->assertExitCode(0);

        // v1 y v2 ya hicieron check-in en portería, así que no se mueven aunque excedan el cupo.
        $this->assertEquals('Dentro del área', $v1->fresh()->ubicacion);
        $this->assertEquals('Dentro del área', $v2->fresh()->ubicacion);
        $this->assertEquals('Fuera del área', $v3->fresh()->ubicacion);
    }
}
