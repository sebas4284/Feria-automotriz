<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteValidacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_se_puede_crear_cliente_sin_telefono(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Cliente Sin Telefono',
            'cita' => '0',
        ])->assertSessionHasErrors('telefono');

        $this->assertDatabaseMissing('clientes', ['nombre' => 'Cliente Sin Telefono']);
    }

    public function test_se_puede_crear_cliente_con_una_red_social_especifica(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Cliente Instagram',
            'telefono' => '3001234567',
            'cita' => '0',
            'medio_entero' => 'Instagram',
        ])->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', ['nombre' => 'Cliente Instagram', 'medio_entero' => 'Instagram']);
    }

    public function test_no_se_puede_crear_cliente_con_redes_sociales_generico(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Cliente Redes',
            'telefono' => '3001234567',
            'cita' => '0',
            'medio_entero' => 'Redes sociales',
        ])->assertSessionHasErrors('medio_entero');
    }

    public function test_cliente_historico_con_redes_sociales_no_se_ve_afectado_sin_tocarlo(): void
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente Viejo',
            'telefono' => '3000000000',
            'cita' => false,
            'medio_entero' => 'Redes sociales',
        ]);

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'medio_entero' => 'Redes sociales']);
    }
}
