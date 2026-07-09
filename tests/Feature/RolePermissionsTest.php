<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $rol, ?Concesionario $concesionario = null): User
    {
        return User::factory()->create([
            'rol' => $rol,
            'concesionario_id' => $concesionario?->id,
        ]);
    }

    public function test_concesionario_only_sees_own_leads_in_index(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);

        $leadA = Lead::create(['meta_lead_id' => 'la', 'full_name' => 'Fulanito Perez Dealer A', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concA->id]);
        $leadB = Lead::create(['meta_lead_id' => 'lb', 'full_name' => 'Menganito Gomez Dealer B', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concB->id]);

        $response = $this->actingAs($userA)->get('/leads');

        $response->assertOk();
        $response->assertSee($leadA->full_name);
        $response->assertDontSee($leadB->full_name);
    }

    public function test_concesionario_cannot_view_lead_of_another_dealer_directly(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);

        $leadB = Lead::create(['meta_lead_id' => 'lb', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concB->id]);

        $this->actingAs($userA)->get("/leads/{$leadB->id}")->assertForbidden();
    }

    public function test_concesionario_cannot_reassign_leads(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);
        $lead = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concA->id]);

        $this->actingAs($userA)
            ->patch("/leads/{$lead->id}/reassign", ['to_concesionario_id' => $concA->id])
            ->assertForbidden();
    }

    public function test_concesionario_cannot_delete_leads(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);
        $lead = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concA->id]);

        $this->actingAs($userA)->delete("/leads/{$lead->id}")->assertForbidden();
    }

    public function test_admin_sees_all_leads(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = $this->makeUser('admin');

        $leadA = Lead::create(['meta_lead_id' => 'la', 'full_name' => 'Fulanito Perez Dealer A', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concA->id]);
        $leadB = Lead::create(['meta_lead_id' => 'lb', 'full_name' => 'Menganito Gomez Dealer B', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concB->id]);

        $response = $this->actingAs($admin)->get('/leads');

        $response->assertOk();
        $response->assertSee($leadA->full_name);
        $response->assertSee($leadB->full_name);
    }

    public function test_concesionario_cannot_access_concesionarios_module(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $user = $this->makeUser('concesionario', $conc);

        $this->actingAs($user)->get('/concesionarios')->assertForbidden();
    }

    public function test_concesionario_cannot_access_usuarios_module(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $user = $this->makeUser('concesionario', $conc);

        $this->actingAs($user)->get('/usuarios')->assertForbidden();
    }

    public function test_concesionario_cannot_access_rifa(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $user = $this->makeUser('concesionario', $conc);

        $this->actingAs($user)->get('/rifa')->assertForbidden();
    }

    public function test_admin_can_access_concesionarios_usuarios_and_rifa(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/concesionarios')->assertOk();
        $this->actingAs($admin)->get('/usuarios')->assertOk();
        $this->actingAs($admin)->get('/rifa')->assertOk();
    }

    public function test_concesionario_sees_all_vehiculos_but_cannot_edit_others(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);

        $vehA = Vehiculo::create(['placa' => 'AAA111', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concA->id]);
        $vehB = Vehiculo::create(['placa' => 'BBB222', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concB->id]);

        $indexResponse = $this->actingAs($userA)->get('/vehiculos');
        $indexResponse->assertOk();
        $indexResponse->assertSee('AAA111');
        $indexResponse->assertSee('BBB222');

        $this->actingAs($userA)->get("/vehiculos/{$vehA->id}/edit")->assertOk();
        $this->actingAs($userA)->get("/vehiculos/{$vehB->id}/edit")->assertForbidden();
    }

    public function test_concesionario_only_sees_own_clientes(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);

        $clienteA = Cliente::create(['nombre' => 'Cliente A', 'concesionario_id' => $concA->id]);
        $clienteB = Cliente::create(['nombre' => 'Cliente B', 'concesionario_id' => $concB->id]);

        $response = $this->actingAs($userA)->get('/clientes');
        $response->assertOk();
        $response->assertSee('Cliente A');
        $response->assertDontSee('Cliente B');

        $this->actingAs($userA)->get("/clientes/{$clienteB->id}")->assertForbidden();
    }

    public function test_register_route_no_longer_exists(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_admin_can_manage_usuarios(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/usuarios')->assertOk();
        $this->actingAs($admin)->get('/usuarios/create')->assertOk();
    }

    public function test_estadisticas_accessible_to_both_roles(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = $this->makeUser('admin');
        $userConc = $this->makeUser('concesionario', $conc);

        $this->actingAs($admin)->get('/estadisticas')->assertOk();
        $this->actingAs($userConc)->get('/estadisticas')->assertOk();
    }

    public function test_concesionarios_and_asesores_show_pages_work_for_admin(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = \App\Models\AsesorComercial::create(['cedula' => '123', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);

        $this->actingAs($admin)->get("/concesionarios/{$conc->id}")->assertOk();
        $this->actingAs($admin)->get("/asesores/{$asesor->id}")->assertOk();
    }

    public function test_ventas_edit_and_destroy_reverts_vehiculo_estado(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = \App\Models\AsesorComercial::create(['cedula' => '123', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $vehiculo = Vehiculo::create(['placa' => 'ZZZ999', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Vendido', 'concesionario_id' => $conc->id]);
        $comprador = \App\Models\Comprador::create(['identificacion' => 'CC1', 'nombre' => 'Comprador Uno']);
        $admin = $this->makeUser('admin');

        $venta = \App\Models\Venta::create([
            'comprador_id' => $comprador->id,
            'vehiculo_id' => $vehiculo->id,
            'concesionario_vende_id' => $conc->id,
            'user_id' => $admin->id,
            'asesor_comercial_id' => $asesor->id,
            'valor' => 1000,
            'fecha_venta' => now(),
            'forma_pago' => 'Contado',
            'participa_experiencia' => false,
        ]);

        $this->actingAs($admin)->delete("/ventas/{$venta->id}")->assertRedirect(route('ventas.index'));

        $this->assertEquals('Disponible', $vehiculo->fresh()->estado);
    }
}
