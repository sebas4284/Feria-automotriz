<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcesionarioCupoTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'placa' => 'CCT' . uniqid(),
            'marca' => 'Marca',
            'linea' => 'Linea',
            'modelo' => 2024,
            'estado' => 'Disponible',
            'ubicacion' => 'Dentro del área',
        ], $overrides);
    }

    private function updatePayload(Concesionario $concesionario, array $overrides = []): array
    {
        return array_merge([
            'nombre' => $concesionario->nombre,
            'nit' => $concesionario->nit,
            'ciudad' => $concesionario->ciudad,
            'telefono' => $concesionario->telefono,
            'email' => $concesionario->email,
            'responsable' => $concesionario->responsable,
            'peso_asignacion' => $concesionario->peso_asignacion,
            'cupo_feria' => $concesionario->cupo_feria,
        ], $overrides);
    }

    public function test_lowering_cupo_demotes_the_newest_excess_vehiculos(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $conc = Concesionario::create(['nombre' => 'Auto 2 SAS', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => null]);

        $v1 = Vehiculo::create($this->payload(['placa' => 'CCT001', 'concesionario_id' => $conc->id]));
        $v2 = Vehiculo::create($this->payload(['placa' => 'CCT002', 'concesionario_id' => $conc->id]));
        $v3 = Vehiculo::create($this->payload(['placa' => 'CCT003', 'concesionario_id' => $conc->id]));

        Vehiculo::where('placa', 'CCT001')->update(['created_at' => now()->subMinutes(3)]);
        Vehiculo::where('placa', 'CCT002')->update(['created_at' => now()->subMinutes(2)]);
        Vehiculo::where('placa', 'CCT003')->update(['created_at' => now()->subMinute()]);

        $response = $this->actingAs($admin)->put(
            "/concesionarios/{$conc->id}",
            $this->updatePayload($conc, ['cupo_feria' => 2])
        );

        $response->assertRedirect(route('concesionarios.index'));
        $response->assertSessionHas('success', function ($mensaje) {
            return str_contains($mensaje, 'Se movieron 1 vehículo(s)');
        });

        $this->assertEquals('Dentro del área', $v1->fresh()->ubicacion);
        $this->assertEquals('Dentro del área', $v2->fresh()->ubicacion);
        $this->assertEquals('Fuera del área', $v3->fresh()->ubicacion);
    }

    public function test_lowering_cupo_does_not_demote_a_vehiculo_already_ingresado(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $conc = Concesionario::create(['nombre' => 'Auto 2 SAS', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => null]);

        $v1 = Vehiculo::create($this->payload(['placa' => 'CCT010', 'concesionario_id' => $conc->id, 'ingresado_at' => now()]));
        $v2 = Vehiculo::create($this->payload(['placa' => 'CCT011', 'concesionario_id' => $conc->id, 'ingresado_at' => now()]));

        $response = $this->actingAs($admin)->put(
            "/concesionarios/{$conc->id}",
            $this->updatePayload($conc, ['cupo_feria' => 1])
        );

        $response->assertRedirect(route('concesionarios.index'));

        // Ambos ya hicieron check-in en portería, así que ninguno se mueve aunque queden por encima del nuevo cupo.
        $this->assertEquals('Dentro del área', $v1->fresh()->ubicacion);
        $this->assertEquals('Dentro del área', $v2->fresh()->ubicacion);
    }

    public function test_raising_cupo_does_not_demote_anything(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $conc = Concesionario::create(['nombre' => 'Auto 2 SAS', 'peso_asignacion' => 1, 'activo' => true, 'cupo_feria' => 1]);

        $v1 = Vehiculo::create($this->payload(['placa' => 'CCT020', 'concesionario_id' => $conc->id]));

        $response = $this->actingAs($admin)->put(
            "/concesionarios/{$conc->id}",
            $this->updatePayload($conc, ['cupo_feria' => 10])
        );

        $response->assertRedirect(route('concesionarios.index'));
        $response->assertSessionHas('success', 'Concesionario actualizado correctamente');

        $this->assertEquals('Dentro del área', $v1->fresh()->ubicacion);
    }
}
