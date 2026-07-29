<?php

namespace Tests\Feature;

use App\Models\AsesorComercial;
use App\Models\Comprador;
use App\Models\Concesionario;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RifaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_rifa_index_shows_vendedor_y_concesionario_y_detalle(): void
    {
        $conc = Concesionario::create(['nombre' => 'Concesionario Prueba', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '111', 'nombre' => 'Vendedor Prueba', 'concesionario_id' => $conc->id]);
        $vehiculo = Vehiculo::create(['placa' => 'RIF001', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Vendido', 'concesionario_id' => $conc->id]);
        $comprador = Comprador::create(['identificacion' => 'CC2', 'nombre' => 'Comprador Dos']);
        $admin = User::factory()->create(['rol' => 'admin']);

        Venta::create([
            'comprador_id' => $comprador->id,
            'vehiculo_id' => $vehiculo->id,
            'concesionario_vende_id' => $conc->id,
            'user_id' => $admin->id,
            'asesor_comercial_id' => $asesor->id,
            'valor' => 1000,
            'fecha_venta' => now(),
            'forma_pago' => 'Contado',
            'participa_experiencia' => true,
            'detalle_experiencia' => 'Cena para 2 personas',
        ]);

        $response = $this->actingAs($admin)->get('/rifa');

        $response->assertOk();
        $response->assertSee('Vendedor Prueba');
        $response->assertSee('Concesionario Prueba');
        $response->assertSee('Cena para 2 personas');
    }

    public function test_creating_venta_with_detalle_experiencia_saves_and_displays_it(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '999', 'nombre' => 'Asesor Prueba', 'concesionario_id' => $conc->id]);
        $vehiculo = Vehiculo::create(['placa' => 'EXP001', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $conc->id]);
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/ventas', [
            'comprador_identificacion' => 'CC-EXP-1',
            'comprador_nombre' => 'Comprador Experiencia',
            'vehiculo_id' => $vehiculo->id,
            'concesionario_vende_id' => $conc->id,
            'asesor_comercial_id' => $asesor->id,
            'valor' => 50000000,
            'fecha_venta' => now()->format('Y-m-d'),
            'forma_pago' => 'Contado',
            'participa_experiencia' => '1',
            'detalle_experiencia' => 'Cena para 2 personas',
        ])->assertRedirect();

        $comprador = Comprador::where('identificacion', 'CC-EXP-1')->firstOrFail();
        $venta = Venta::where('comprador_id', $comprador->id)->firstOrFail();

        $this->assertEquals('Cena para 2 personas', $venta->detalle_experiencia);

        $this->actingAs($admin)->get(route('ventas.show', $venta))->assertSee('Cena para 2 personas');
        $this->actingAs($admin)->get('/rifa')->assertSee('Cena para 2 personas');
    }

    public function test_venta_without_detalle_experiencia_is_saved_as_null(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '888', 'nombre' => 'Asesor Prueba', 'concesionario_id' => $conc->id]);
        $vehiculo = Vehiculo::create(['placa' => 'EXP002', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $conc->id]);
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)->post('/ventas', [
            'comprador_identificacion' => 'CC-EXP-2',
            'comprador_nombre' => 'Comprador Sin Detalle',
            'vehiculo_id' => $vehiculo->id,
            'concesionario_vende_id' => $conc->id,
            'asesor_comercial_id' => $asesor->id,
            'valor' => 50000000,
            'fecha_venta' => now()->format('Y-m-d'),
            'forma_pago' => 'Contado',
        ])->assertRedirect();

        $comprador = Comprador::where('identificacion', 'CC-EXP-2')->firstOrFail();
        $venta = Venta::where('comprador_id', $comprador->id)->firstOrFail();

        $this->assertNull($venta->detalle_experiencia);
    }

    public function test_boleta_uses_exp_prefix(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '222', 'nombre' => 'Asesor Tres', 'concesionario_id' => $conc->id]);
        $vehiculo = Vehiculo::create(['placa' => 'BOL001', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Vendido', 'concesionario_id' => $conc->id]);
        $comprador = Comprador::create(['identificacion' => 'CC3', 'nombre' => 'Comprador Tres']);
        $admin = User::factory()->create(['rol' => 'admin']);

        $venta = Venta::create([
            'comprador_id' => $comprador->id,
            'vehiculo_id' => $vehiculo->id,
            'concesionario_vende_id' => $conc->id,
            'user_id' => $admin->id,
            'asesor_comercial_id' => $asesor->id,
            'valor' => 1000,
            'fecha_venta' => now(),
            'forma_pago' => 'Contado',
            'participa_experiencia' => true,
        ]);

        $this->assertStringStartsWith('EXP-', $venta->boleta);
    }
}
