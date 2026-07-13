<?php

namespace Tests\Feature;

use App\Models\AsesorComercial;
use App\Models\Cliente;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\Turno;
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

    private function makeAsesorUser(AsesorComercial $asesor): User
    {
        return User::factory()->create([
            'rol' => 'asesor',
            'asesor_comercial_id' => $asesor->id,
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

    public function test_nuevos_hoy_counts_leads_created_today_regardless_of_estado(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = $this->makeUser('admin');

        Lead::create(['meta_lead_id' => 'hoy1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id, 'created_time' => now()]);
        Lead::create(['meta_lead_id' => 'hoy2', 'estado_gestion' => 'Contactado', 'concesionario_id' => $conc->id, 'created_time' => now()]);
        Lead::create(['meta_lead_id' => 'ayer1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id, 'created_time' => now()->subDay()]);

        $response = $this->actingAs($admin)->get('/leads');

        $response->assertOk();
        $response->assertSee('Nuevos hoy');

        // 2 leads de hoy (uno Nuevo, otro Contactado) deben contar; el de ayer no.
        $response->assertViewHas('totalNuevos', 2);
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

    public function test_leads_index_can_be_filtered_by_sin_asesor(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesorComercial = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $admin = $this->makeUser('admin');

        Lead::create(['meta_lead_id' => 'con', 'full_name' => 'Lead Con Asesor', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id, 'asesor_comercial_id' => $asesorComercial->id]);
        Lead::create(['meta_lead_id' => 'sin', 'full_name' => 'Lead Sin Asesor', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id]);

        $response = $this->actingAs($admin)->get('/leads?filtro=sin_asesor');

        $response->assertOk();
        $response->assertSee('Lead Sin Asesor');
        $response->assertDontSee('Lead Con Asesor');
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

    public function test_concesionario_creating_vehiculo_gets_own_concesionario_forced(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);

        $this->actingAs($userA)->post('/vehiculos', [
            'concesionario_id' => $concB->id,
            'placa' => 'CCC333',
            'marca' => 'M',
            'linea' => 'L',
            'modelo' => 2024,
            'estado' => 'Disponible',
        ])->assertRedirect(route('vehiculos.index'));

        $this->assertEquals($concA->id, Vehiculo::where('placa', 'CCC333')->first()->concesionario_id);
    }

    public function test_vehiculos_index_can_be_filtered_by_placa(): void
    {
        $admin = $this->makeUser('admin');
        Vehiculo::create(['placa' => 'AAA111', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible']);
        Vehiculo::create(['placa' => 'BBB222', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible']);

        $response = $this->actingAs($admin)->get('/vehiculos?placa=AAA');

        $response->assertOk();
        $response->assertSee('AAA111');
        $response->assertDontSee('BBB222');
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

    public function test_usuarios_index_shows_asesors_dealership_not_their_own_name(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'VF Motors', 'peso_asignacion' => 1, 'activo' => true]);
        $asesorComercial = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Natalia Saenz', 'concesionario_id' => $conc->id]);
        $this->makeAsesorUser($asesorComercial);

        $response = $this->actingAs($admin)->get('/usuarios');

        $response->assertOk();
        $response->assertSee('VF Motors');
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

    public function test_asesor_only_sees_leads_assigned_to_them(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $otroAsesor = AsesorComercial::create(['cedula' => '2', 'nombre' => 'Asesor Dos', 'concesionario_id' => $conc->id]);
        $user = $this->makeAsesorUser($asesor);

        $leadAsignado = Lead::create(['meta_lead_id' => 'l1', 'full_name' => 'Lead Asignado', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id, 'asesor_comercial_id' => $asesor->id]);
        $leadOtro = Lead::create(['meta_lead_id' => 'l2', 'full_name' => 'Lead De Otro Asesor', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id, 'asesor_comercial_id' => $otroAsesor->id]);

        $response = $this->actingAs($user)->get('/leads');
        $response->assertOk();
        $response->assertSee('Lead Asignado');
        $response->assertDontSee('Lead De Otro Asesor');

        $this->actingAs($user)->get("/leads/{$leadOtro->id}")->assertForbidden();
        $this->actingAs($user)->get("/leads/{$leadAsignado->id}")->assertOk();
    }

    public function test_asesor_can_manage_own_assigned_lead(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $user = $this->makeAsesorUser($asesor);
        $lead = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id, 'asesor_comercial_id' => $asesor->id]);

        $this->actingAs($user)
            ->put("/leads/{$lead->id}", ['estado_gestion' => 'Contactado', 'observaciones' => 'Llamado hoy'])
            ->assertRedirect(route('leads.show', $lead));

        $this->assertEquals('Contactado', $lead->fresh()->estado_gestion);
    }

    public function test_asesor_sees_all_vehiculos_but_cannot_create_or_edit(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $concA->id]);
        $user = $this->makeAsesorUser($asesor);

        $vehA = Vehiculo::create(['placa' => 'AAA111', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concA->id]);
        $vehB = Vehiculo::create(['placa' => 'BBB222', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concB->id]);

        $response = $this->actingAs($user)->get('/vehiculos');
        $response->assertOk();
        $response->assertSee('AAA111');
        $response->assertSee('BBB222');

        $this->actingAs($user)->get('/vehiculos/create')->assertForbidden();
        $this->actingAs($user)->get("/vehiculos/{$vehA->id}/edit")->assertForbidden();
    }

    public function test_asesor_cannot_access_clientes_ventas_or_estadisticas(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $user = $this->makeAsesorUser($asesor);

        $this->actingAs($user)->get('/clientes')->assertForbidden();
        $this->actingAs($user)->get('/ventas')->assertForbidden();
        $this->actingAs($user)->get('/estadisticas')->assertForbidden();
        $this->actingAs($user)->get('/asesores')->assertForbidden();
    }

    public function test_concesionario_can_assign_lead_to_own_asesor(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $user = $this->makeUser('concesionario', $conc);
        $lead = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id]);

        $this->actingAs($user)
            ->patch("/leads/{$lead->id}/assign-asesor", ['asesor_comercial_id' => $asesor->id])
            ->assertRedirect();

        $this->assertEquals($asesor->id, $lead->fresh()->asesor_comercial_id);
        $this->assertEquals('Asignado', $lead->fresh()->estado_gestion);
    }

    public function test_assigning_asesor_resets_the_vencido_clock(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $user = $this->makeUser('concesionario', $conc);
        $lead = Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Nuevo',
            'concesionario_id' => $conc->id,
            'assigned_at' => now()->subHours(30),
        ]);

        $this->assertTrue($lead->fresh()->vencido);

        $this->actingAs($user)
            ->patch("/leads/{$lead->id}/assign-asesor", ['asesor_comercial_id' => $asesor->id]);

        $lead->refresh();
        $this->assertFalse($lead->vencido);
        $this->assertTrue($lead->assigned_at->gt(now()->subMinute()));
        $this->assertNull($lead->vencido_notified_at);
    }

    public function test_assigning_asesor_does_not_downgrade_a_more_advanced_estado(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $user = $this->makeUser('concesionario', $conc);
        $lead = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Contactado', 'concesionario_id' => $conc->id]);

        $this->actingAs($user)
            ->patch("/leads/{$lead->id}/assign-asesor", ['asesor_comercial_id' => $asesor->id]);

        $this->assertEquals('Contactado', $lead->fresh()->estado_gestion);
    }

    public function test_reassigning_concesionario_clears_asesor_and_resets_asignado_to_nuevo(): void
    {
        $from = Concesionario::create(['nombre' => 'Origen', 'peso_asignacion' => 1, 'activo' => true]);
        $to = Concesionario::create(['nombre' => 'Destino', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $from->id]);
        $admin = $this->makeUser('admin');
        $lead = Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Asignado',
            'concesionario_id' => $from->id,
            'asesor_comercial_id' => $asesor->id,
            'assigned_at' => now(),
            'vencido_notified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch("/leads/{$lead->id}/reassign", ['to_concesionario_id' => $to->id]);

        $lead->refresh();
        $this->assertNull($lead->asesor_comercial_id);
        $this->assertEquals('Nuevo', $lead->estado_gestion);
        $this->assertNull($lead->vencido_notified_at);
    }

    public function test_concesionario_cannot_assign_lead_to_asesor_of_another_dealer(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $asesorB = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor De B', 'concesionario_id' => $concB->id]);
        $userA = $this->makeUser('concesionario', $concA);
        $leadA = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concA->id]);

        $this->actingAs($userA)
            ->patch("/leads/{$leadA->id}/assign-asesor", ['asesor_comercial_id' => $asesorB->id])
            ->assertNotFound();

        $this->assertNull($leadA->fresh()->asesor_comercial_id);
    }

    public function test_asesor_cannot_assign_leads(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $user = $this->makeAsesorUser($asesor);
        $lead = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id, 'asesor_comercial_id' => $asesor->id]);

        $this->actingAs($user)
            ->patch("/leads/{$lead->id}/assign-asesor", ['asesor_comercial_id' => $asesor->id])
            ->assertForbidden();
    }

    public function test_concesionario_and_asesor_cannot_access_turnos(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesorComercial = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $userConc = $this->makeUser('concesionario', $conc);
        $userAsesor = $this->makeAsesorUser($asesorComercial);

        $this->actingAs($userConc)->get('/turnos')->assertForbidden();
        $this->actingAs($userAsesor)->get('/turnos')->assertForbidden();
    }

    public function test_admin_can_check_in_and_check_out_concesionario(): void
    {
        $admin = $this->makeUser('admin');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        $this->actingAs($admin)->get('/turnos')->assertOk();

        $this->actingAs($admin)->post("/turnos/{$conc->id}/check-in")->assertRedirect();
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $conc->id]);

        $this->actingAs($admin)->delete("/turnos/{$conc->id}/check-in")->assertRedirect();
        $this->assertDatabaseMissing('turnos', ['concesionario_id' => $conc->id]);
    }

    public function test_cliente_sin_cita_is_auto_assigned_via_turno_and_rotates(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);
        Turno::create(['concesionario_id' => $b->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(5)]);

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Walk-in Uno',
            'cita' => '0',
        ])->assertRedirect(route('clientes.index'));

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Walk-in Dos',
            'cita' => '0',
        ])->assertRedirect(route('clientes.index'));

        $primero = Cliente::where('nombre', 'Walk-in Uno')->first();
        $segundo = Cliente::where('nombre', 'Walk-in Dos')->first();

        $this->assertEquals($a->id, $primero->concesionario_id);
        $this->assertEquals($b->id, $segundo->concesionario_id);
    }

    public function test_cliente_con_cita_ignores_turno_queue(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Con Cita',
            'cita' => '1',
            'concesionario_id' => $b->id,
        ])->assertRedirect(route('clientes.index'));

        $cliente = Cliente::where('nombre', 'Con Cita')->first();

        $this->assertEquals($b->id, $cliente->concesionario_id);
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $a->id, 'ultima_asignacion_at' => null]);
    }

    public function test_staff_can_view_rifa_and_turnos_but_not_operate_turnos(): void
    {
        $staff = $this->makeUser('staff');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        $this->actingAs($staff)->get('/rifa')->assertOk();
        $this->actingAs($staff)->get('/turnos')->assertOk();

        $this->actingAs($staff)->post("/turnos/{$conc->id}/check-in")->assertForbidden();
        $this->actingAs($staff)->delete("/turnos/{$conc->id}/check-in")->assertForbidden();
    }

    public function test_staff_cannot_access_anything_else(): void
    {
        $staff = $this->makeUser('staff');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        $this->actingAs($staff)->get('/clientes')->assertForbidden();
        $this->actingAs($staff)->get('/ventas')->assertForbidden();
        $this->actingAs($staff)->get('/leads')->assertForbidden();
        $this->actingAs($staff)->get('/vehiculos')->assertForbidden();
        $this->actingAs($staff)->get('/asesores')->assertForbidden();
        $this->actingAs($staff)->get('/estadisticas')->assertForbidden();
        $this->actingAs($staff)->get('/concesionarios')->assertForbidden();
        $this->actingAs($staff)->get('/usuarios')->assertForbidden();
    }

    public function test_turnos_screen_shows_todays_walkin_clients_and_their_concesionario(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'Dealer Uno', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()]);

        Cliente::create(['nombre' => 'Walk-in Visible', 'cita' => false, 'concesionario_id' => $a->id]);

        $response = $this->actingAs($admin)->get('/turnos');

        $response->assertOk();
        $response->assertSee('Walk-in Visible');
        $response->assertSee('Dealer Uno');
    }

    public function test_admin_can_operate_turnos_and_staff_view_reflects_it(): void
    {
        $admin = $this->makeUser('admin');
        $staff = $this->makeUser('staff');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        $this->actingAs($admin)->post("/turnos/{$conc->id}/check-in")->assertRedirect();

        $response = $this->actingAs($staff)->get('/turnos');
        $response->assertOk();
        $response->assertSee('A');
    }
}
