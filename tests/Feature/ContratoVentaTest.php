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

class ContratoVentaTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $rol, ?Concesionario $concesionario = null): User
    {
        return User::factory()->create([
            'rol' => $rol,
            'concesionario_id' => $concesionario?->id,
        ]);
    }

    /**
     * Crea una venta con dueño y vendedor opcionalmente distintos (venta cruzada).
     */
    private function crearVenta(User $admin, Concesionario $dueno, ?Concesionario $vendedor, string $placa): Venta
    {
        $vendedor ??= $dueno;

        $asesor = AsesorComercial::create([
            'cedula' => 'AS' . $placa,
            'nombre' => 'Asesor ' . $placa,
            'concesionario_id' => $vendedor->id,
        ]);
        $vehiculo = Vehiculo::create([
            'placa' => $placa,
            'marca' => 'M',
            'modelo' => 2024,
            'estado' => 'Vendido',
            'concesionario_id' => $dueno->id,
        ]);
        $comprador = Comprador::create([
            'identificacion' => 'CC' . $placa,
            'nombre' => 'Comprador ' . $placa,
        ]);

        return Venta::create([
            'comprador_id' => $comprador->id,
            'vehiculo_id' => $vehiculo->id,
            'concesionario_vende_id' => $vendedor->id,
            'user_id' => $admin->id,
            'asesor_comercial_id' => $asesor->id,
            'valor' => 50_000_000,
            'fecha_venta' => now(),
            'forma_pago' => 'Contado',
            'participa_experiencia' => false,
        ]);
    }

    public function test_admin_puede_ver_y_actualizar_contrato_de_venta(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'Contrato Motors', 'peso_asignacion' => 1, 'activo' => true]);
        $venta = $this->crearVenta($admin, $conc, null, 'CTR001');

        $this->actingAs($admin)->get(route('ventas.contrato', $venta))->assertOk();

        $this->actingAs($admin)->patch(route('ventas.contrato.update', $venta), [
            'comprador_tipo_documento' => 'CC',
            'comprador_lugar_expedicion' => 'Cali',
            'comprador_fecha_expedicion' => '2015-01-01',
            'ciudad_firma' => 'Cali',
            'dias_traspaso' => 15,
            'porcentaje_gastos_vendedor' => 50,
            'porcentaje_gastos_comprador' => 50,
            'clausula_penal_smmlv' => 10,
            'testigo_nombre' => 'Juan Testigo',
            'testigo_identificacion' => '123456',
        ])->assertRedirect(route('ventas.contrato', $venta));

        $venta->refresh();
        $venta->load('comprador');

        $this->assertSame('Cali', $venta->ciudad_firma);
        $this->assertSame(15, $venta->dias_traspaso);
        $this->assertSame(50, $venta->porcentaje_gastos_vendedor);
        $this->assertSame('Juan Testigo', $venta->testigo_nombre);
        $this->assertSame('CC', $venta->comprador->tipo_documento);
        $this->assertSame('Cali', $venta->comprador->lugar_expedicion);
        $this->assertSame('2015-01-01', $venta->comprador->fecha_expedicion->format('Y-m-d'));
    }

    public function test_dueno_del_vehiculo_puede_ver_y_actualizar_contrato(): void
    {
        $admin = $this->makeUser('admin');
        $dueno = Concesionario::create(['nombre' => 'Dueño Motors', 'peso_asignacion' => 1, 'activo' => true]);
        $vendedor = Concesionario::create(['nombre' => 'Vendedor Motors', 'peso_asignacion' => 1, 'activo' => true]);
        $venta = $this->crearVenta($admin, $dueno, $vendedor, 'CTR002');

        $usuarioDueno = $this->makeUser('concesionario', $dueno);

        $this->actingAs($usuarioDueno)->get(route('ventas.contrato', $venta))->assertOk();
        $this->actingAs($usuarioDueno)->patch(route('ventas.contrato.update', $venta), [
            'comprador_tipo_documento' => 'CC',
        ])->assertRedirect(route('ventas.contrato', $venta));
    }

    public function test_concesionario_vendedor_cruzado_no_dueno_no_puede_acceder_al_contrato(): void
    {
        $admin = $this->makeUser('admin');
        $dueno = Concesionario::create(['nombre' => 'Dueño Cruzado', 'peso_asignacion' => 1, 'activo' => true]);
        $vendedor = Concesionario::create(['nombre' => 'Vendedor Cruzado', 'peso_asignacion' => 1, 'activo' => true]);
        $venta = $this->crearVenta($admin, $dueno, $vendedor, 'CTR003');

        $usuarioVendedor = $this->makeUser('concesionario', $vendedor);

        $this->actingAs($usuarioVendedor)->get(route('ventas.contrato', $venta))->assertForbidden();
        $this->actingAs($usuarioVendedor)->patch(route('ventas.contrato.update', $venta), [
            'comprador_tipo_documento' => 'CC',
        ])->assertForbidden();
        $this->actingAs($usuarioVendedor)->get(route('ventas.contrato.pdf', $venta))->assertForbidden();
    }

    public function test_aseguradora_no_puede_acceder_al_contrato(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'Aseguradora Motors', 'peso_asignacion' => 1, 'activo' => true]);
        $venta = $this->crearVenta($admin, $conc, null, 'CTR004');

        $aseguradora = $this->makeUser('aseguradora');

        $this->actingAs($aseguradora)->get(route('ventas.contrato', $venta))->assertForbidden();
    }

    public function test_actualizar_contrato_valida_porcentajes_y_fecha_expedicion_futura(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'Validacion Motors', 'peso_asignacion' => 1, 'activo' => true]);
        $venta = $this->crearVenta($admin, $conc, null, 'CTR005');

        $this->actingAs($admin)->patch(route('ventas.contrato.update', $venta), [
            'comprador_tipo_documento' => 'CC',
            'porcentaje_gastos_vendedor' => 150,
        ])->assertSessionHasErrors('porcentaje_gastos_vendedor');

        $this->actingAs($admin)->patch(route('ventas.contrato.update', $venta), [
            'comprador_tipo_documento' => 'CC',
            'comprador_fecha_expedicion' => now()->addDay()->format('Y-m-d'),
        ])->assertSessionHasErrors('comprador_fecha_expedicion');
    }

    public function test_pdf_del_contrato_muestra_al_dueno_del_vehiculo_no_al_vendedor_cruzado(): void
    {
        $admin = $this->makeUser('admin');
        $dueno = Concesionario::create(['nombre' => 'Dueño PDF', 'peso_asignacion' => 1, 'activo' => true]);
        $vendedor = Concesionario::create(['nombre' => 'Vendedor PDF', 'peso_asignacion' => 1, 'activo' => true]);
        $venta = $this->crearVenta($admin, $dueno, $vendedor, 'CTR006');

        $response = $this->actingAs($admin)->get(route('ventas.contrato.pdf', $venta));

        $response->assertOk();
        $response->assertSee('Dueño PDF');
        $response->assertSee($venta->comprador->nombre);
        $response->assertSee('CTR006');
    }
}
