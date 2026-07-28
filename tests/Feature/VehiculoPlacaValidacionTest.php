<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiculoPlacaValidacionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'placa' => 'ABC123',
            'marca' => 'Marca',
            'linea' => 'Linea',
            'modelo' => 2024,
            'estado' => 'Disponible',
            'ubicacion' => 'Dentro del área',
        ], $overrides);
    }

    public function test_no_se_puede_crear_vehiculo_con_placa_de_menos_de_6_caracteres(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/vehiculos', $this->payload(['placa' => 'ABC12']))
            ->assertSessionHasErrors('placa');

        $this->assertDatabaseMissing('vehiculos', ['placa' => 'ABC12']);
    }

    public function test_no_se_puede_crear_vehiculo_con_placa_de_mas_de_6_caracteres(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/vehiculos', $this->payload(['placa' => 'ABC1234']))
            ->assertSessionHasErrors('placa');

        $this->assertDatabaseMissing('vehiculos', ['placa' => 'ABC1234']);
    }

    public function test_no_se_puede_repetir_placa_al_crear(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        Vehiculo::create($this->payload(['placa' => 'ABC123']));

        $this->actingAs($admin)->post('/vehiculos', $this->payload(['placa' => 'ABC123']))
            ->assertSessionHasErrors('placa');

        $this->assertSame(1, Vehiculo::where('placa', 'ABC123')->count());
    }

    public function test_no_se_puede_repetir_placa_al_editar_para_que_coincida_con_otro_vehiculo(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        Vehiculo::create($this->payload(['placa' => 'ABC123']));
        $otro = Vehiculo::create($this->payload(['placa' => 'XYZ789']));

        $this->actingAs($admin)->put("/vehiculos/{$otro->id}", $this->payload(['placa' => 'ABC123']))
            ->assertSessionHasErrors('placa');

        $this->assertSame('XYZ789', $otro->fresh()->placa);
    }

    public function test_editar_un_vehiculo_sin_cambiar_su_propia_placa_no_falla(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $vehiculo = Vehiculo::create($this->payload(['placa' => 'ABC123']));

        $this->actingAs($admin)->put("/vehiculos/{$vehiculo->id}", $this->payload(['placa' => 'ABC123']))
            ->assertRedirect(route('vehiculos.show', $vehiculo));

        $this->assertSame('ABC123', $vehiculo->fresh()->placa);
    }
}
