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

    public function test_concesionario_sees_own_vehiculos_first_in_index(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);

        // B se crea después de A, así que por "más reciente primero" saldría primero
        // si no se priorizara el concesionario propio.
        Vehiculo::create(['placa' => 'AAA111', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concA->id]);
        Vehiculo::create(['placa' => 'BBB222', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concB->id]);

        $this->actingAs($userA)->get('/vehiculos')->assertSeeInOrder(['AAA111', 'BBB222']);
    }

    public function test_asesor_sees_own_concesionario_vehiculos_first_in_index(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $concA->id]);
        $userAsesor = $this->makeAsesorUser($asesor);

        Vehiculo::create(['placa' => 'AAA111', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concA->id]);
        Vehiculo::create(['placa' => 'BBB222', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concB->id]);

        $this->actingAs($userAsesor)->get('/vehiculos')->assertSeeInOrder(['AAA111', 'BBB222']);
    }

    public function test_admin_sees_vehiculos_in_default_latest_order(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = $this->makeUser('admin');

        Vehiculo::create(['placa' => 'AAA111', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concA->id]);
        Vehiculo::create(['placa' => 'BBB222', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concB->id]);

        // Sin concesionario propio, el admin sigue viendo el orden por defecto: más reciente primero.
        $this->actingAs($admin)->get('/vehiculos')->assertSeeInOrder(['BBB222', 'AAA111']);
    }

    public function test_concesionario_kpi_counts_are_scoped_to_own_vehiculos(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);

        Vehiculo::create(['placa' => 'AAA111', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concA->id, 'ubicacion' => 'Dentro del área']);
        Vehiculo::create(['placa' => 'AAA222', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concA->id, 'ubicacion' => 'Fuera del área']);
        Vehiculo::create(['placa' => 'BBB111', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concB->id, 'ubicacion' => 'Dentro del área']);
        Vehiculo::create(['placa' => 'BBB222', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concB->id, 'ubicacion' => 'Dentro del área']);
        Vehiculo::create(['placa' => 'BBB333', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'concesionario_id' => $concB->id, 'ubicacion' => 'Fuera del área']);

        $response = $this->actingAs($userA)->get('/vehiculos');

        $response->assertOk();
        // A tiene 1 "Dentro del área" y 1 "Fuera del área" propios; B tiene 2 y 1 respectivamente.
        // Si el KPI no estuviera acotado por concesionario, el tile mostraría 3 (agregado) en vez de 1 para "Dentro del área".
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/<p[^>]*>\s*1\s*<\/p>\s*<p[^>]*>\s*Dentro del área\s*<\/p>/u', $html);
        $this->assertMatchesRegularExpression('/<p[^>]*>\s*1\s*<\/p>\s*<p[^>]*>\s*Fuera del área\s*<\/p>/u', $html);
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
            'ubicacion' => 'Dentro del área',
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

    public function test_vehiculos_index_search_also_matches_marca_y_linea(): void
    {
        $admin = $this->makeUser('admin');
        Vehiculo::create(['placa' => 'CCC333', 'marca' => 'Toyota', 'linea' => 'Corolla', 'modelo' => 2024, 'estado' => 'Disponible']);
        Vehiculo::create(['placa' => 'DDD444', 'marca' => 'Mazda', 'linea' => 'CX5', 'modelo' => 2024, 'estado' => 'Disponible']);

        $response = $this->actingAs($admin)->get('/vehiculos?placa=Toyota');

        $response->assertOk();
        $response->assertSee('CCC333');
        $response->assertDontSee('DDD444');
    }

    public function test_texto_de_busqueda_ignora_el_filtro_de_ubicacion(): void
    {
        $admin = $this->makeUser('admin');
        Vehiculo::create(['placa' => 'EEE555', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'ubicacion' => 'Dentro del área']);
        Vehiculo::create(['placa' => 'EEE556', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'ubicacion' => 'Fuera del área']);

        // Con texto de búsqueda ("EEE55"), el filtro de ubicación se ignora: deben verse ambos.
        $response = $this->actingAs($admin)->get('/vehiculos?placa=EEE55&ubicacion=Dentro del área');

        $response->assertOk();
        $response->assertSee('EEE555');
        $response->assertSee('EEE556');
    }

    public function test_filtro_de_ubicacion_solo_sin_texto_de_busqueda(): void
    {
        $admin = $this->makeUser('admin');
        Vehiculo::create(['placa' => 'FFF777', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'ubicacion' => 'Dentro del área']);
        Vehiculo::create(['placa' => 'FFF778', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Disponible', 'ubicacion' => 'Fuera del área']);

        // Sin texto de búsqueda, el filtro de ubicación sigue funcionando igual que antes.
        $response = $this->actingAs($admin)->get('/vehiculos?ubicacion=Dentro del área');

        $response->assertOk();
        $response->assertSee('FFF777');
        $response->assertDontSee('FFF778');
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

    public function test_estadisticas_shows_leads_por_concesionario_only_to_admin(): void
    {
        $concA = Concesionario::create(['nombre' => 'Concesionario A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'Concesionario B', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = $this->makeUser('admin');
        $userA = $this->makeUser('concesionario', $concA);

        Lead::create(['meta_lead_id' => 'l1', 'full_name' => 'Lead Uno', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concA->id]);
        Lead::create(['meta_lead_id' => 'l2', 'full_name' => 'Lead Dos', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concA->id]);
        Lead::create(['meta_lead_id' => 'l3', 'full_name' => 'Lead Tres', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concB->id]);

        $adminResponse = $this->actingAs($admin)->get('/estadisticas');
        $adminResponse->assertOk();
        $adminResponse->assertSee('Leads por concesionario');
        $adminResponse->assertSeeInOrder(['Concesionario A', '2']);
        $adminResponse->assertSeeInOrder(['Concesionario B', '1']);

        $this->actingAs($userA)->get('/estadisticas')->assertDontSee('Leads por concesionario');
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

    public function test_ventas_index_shows_placa_concesionario_y_asesor(): void
    {
        $conc = Concesionario::create(['nombre' => 'Concesionario Vendedor', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = \App\Models\AsesorComercial::create(['cedula' => '999', 'nombre' => 'Asesor Vendedor', 'concesionario_id' => $conc->id]);
        $vehiculo = Vehiculo::create(['placa' => 'VTA123', 'marca' => 'M', 'modelo' => 2024, 'estado' => 'Vendido', 'concesionario_id' => $conc->id]);
        $comprador = \App\Models\Comprador::create(['identificacion' => 'CCV1', 'nombre' => 'Comprador Venta']);
        $admin = $this->makeUser('admin');

        \App\Models\Venta::create([
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

        $response = $this->actingAs($admin)->get('/ventas');

        $response->assertOk();
        $response->assertSee('VTA123');
        $response->assertSee('Concesionario Vendedor');
        $response->assertSee('Asesor Vendedor');
    }

    public function test_ventas_create_form_renders_with_buscador_de_vehiculo(): void
    {
        $admin = $this->makeUser('admin');
        Vehiculo::create(['placa' => 'BUS123', 'marca' => 'Mazda', 'modelo' => 2024, 'estado' => 'Disponible']);

        $response = $this->actingAs($admin)->get('/ventas/create');

        $response->assertOk();
        $response->assertSee('Buscar por placa, marca o línea...');
        $response->assertDontSee('<select name="vehiculo_id"', false);
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

    public function test_asesor_sees_all_vehiculos_but_can_only_create_and_edit_for_own_concesionario(): void
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

        $this->actingAs($user)->get('/vehiculos/create')->assertOk();
        $this->actingAs($user)->get("/vehiculos/{$vehA->id}/edit")->assertOk();
        $this->actingAs($user)->get("/vehiculos/{$vehB->id}/edit")->assertForbidden();

        $this->actingAs($user)->post('/vehiculos', [
            'placa' => 'CCC333',
            'marca' => 'M',
            'linea' => 'L',
            'modelo' => 2024,
            'estado' => 'Disponible',
            'ubicacion' => 'Dentro del área',
        ])->assertRedirect(route('vehiculos.index'));

        $this->assertDatabaseHas('vehiculos', ['placa' => 'CCC333', 'concesionario_id' => $concA->id]);
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

    public function test_cliente_sin_cita_queda_pendiente_sin_concesionario(): void
    {
        // El cliente sin cita ya no se auto-asigna al guardar: queda
        // pendiente para que alguien lo arrastre en /turnos (ver
        // TurnoController::asignarCliente).
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Walk-in Uno',
            'telefono' => '3001234567',
            'cita' => '0',
        ])->assertRedirect(route('clientes.index'));

        $cliente = Cliente::where('nombre', 'Walk-in Uno')->first();

        $this->assertNull($cliente->concesionario_id);
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $a->id, 'veces_asignado' => 0]);
    }

    public function test_cliente_con_cita_ignores_turno_queue(): void
    {
        $admin = $this->makeUser('admin');
        $a = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $b = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);

        Turno::create(['concesionario_id' => $a->id, 'fecha' => today(), 'llegada_at' => now()->subMinutes(10)]);

        $this->actingAs($admin)->post('/clientes', [
            'nombre' => 'Con Cita',
            'telefono' => '3001234567',
            'cita' => '1',
            'concesionario_id' => $b->id,
        ])->assertRedirect(route('clientes.index'));

        $cliente = Cliente::where('nombre', 'Con Cita')->first();

        $this->assertEquals($b->id, $cliente->concesionario_id);
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $a->id, 'ultima_asignacion_at' => null]);
    }

    public function test_staff_can_view_rifa_and_operate_turnos(): void
    {
        $staff = $this->makeUser('staff');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        $this->actingAs($staff)->get('/rifa')->assertOk();
        $this->actingAs($staff)->get('/turnos')->assertOk();

        $this->actingAs($staff)->post("/turnos/{$conc->id}/check-in")->assertRedirect();
        $this->assertDatabaseHas('turnos', ['concesionario_id' => $conc->id]);

        $this->actingAs($staff)->post("/turnos/{$conc->id}/saltar")->assertRedirect();

        $this->actingAs($staff)->delete("/turnos/{$conc->id}/check-in")->assertRedirect();
        $this->assertDatabaseMissing('turnos', ['concesionario_id' => $conc->id]);
    }

    public function test_staff_can_assign_a_pendiente_cliente_to_a_concesionario_en_fila(): void
    {
        $staff = $this->makeUser('staff');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        Turno::create(['concesionario_id' => $conc->id, 'fecha' => today(), 'llegada_at' => now()]);
        $cliente = Cliente::create(['nombre' => 'Pendiente Uno', 'cita' => false, 'concesionario_id' => null]);

        $this->actingAs($staff)->postJson('/turnos/asignar-cliente', [
            'cliente_id' => $cliente->id,
            'concesionario_id' => $conc->id,
        ])->assertOk();

        $this->assertSame($conc->id, $cliente->fresh()->concesionario_id);
    }

    public function test_staff_cannot_access_anything_else(): void
    {
        $staff = $this->makeUser('staff');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        $this->actingAs($staff)->get('/ventas')->assertForbidden();
        $this->actingAs($staff)->get('/leads')->assertForbidden();
        $this->actingAs($staff)->get('/vehiculos')->assertForbidden();
        $this->actingAs($staff)->get('/asesores')->assertForbidden();
        $this->actingAs($staff)->get('/estadisticas')->assertForbidden();
        $this->actingAs($staff)->get('/concesionarios')->assertForbidden();
        $this->actingAs($staff)->get('/usuarios')->assertForbidden();
    }

    public function test_staff_can_register_and_view_clientes_but_not_edit_or_delete(): void
    {
        $staff = $this->makeUser('staff');
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $cliente = Cliente::create(['nombre' => 'Cliente De Otro', 'telefono' => '3000000000', 'cita' => true, 'concesionario_id' => $conc->id]);

        // Ve la lista completa (no queda vacía por no tener concesionario propio) y el formulario de registro.
        $this->actingAs($staff)->get('/clientes')->assertOk()->assertSee('Cliente De Otro');
        $this->actingAs($staff)->get('/clientes/create')->assertOk();

        // Puede registrar un cliente nuevo (walk-in, sin cita).
        $this->actingAs($staff)->post('/clientes', [
            'nombre' => 'Walkin Registrado Por Staff',
            'telefono' => '3001112233',
            'cita' => '0',
        ])->assertRedirect(route('clientes.index'));
        $this->assertDatabaseHas('clientes', ['nombre' => 'Walkin Registrado Por Staff', 'concesionario_id' => null]);

        // Puede ver el detalle de cualquier cliente, pero no editarlo ni eliminarlo.
        $this->actingAs($staff)->get("/clientes/{$cliente->id}")->assertOk();
        $this->actingAs($staff)->get("/clientes/{$cliente->id}/edit")->assertForbidden();
        $this->actingAs($staff)->put("/clientes/{$cliente->id}", ['nombre' => 'Hackeado', 'telefono' => '1'])->assertForbidden();
        $this->actingAs($staff)->delete("/clientes/{$cliente->id}")->assertForbidden();
    }

    public function test_staff_sees_all_concesionarios_and_can_assign_one_when_registering_a_cliente_con_cita(): void
    {
        $staff = $this->makeUser('staff');
        $conc = Concesionario::create(['nombre' => 'Concesionario Elegido', 'peso_asignacion' => 1, 'activo' => true]);

        $this->actingAs($staff)->get('/clientes/create')->assertOk()->assertSee('Concesionario Elegido');

        $this->actingAs($staff)->post('/clientes', [
            'nombre' => 'Cliente Con Cita De Staff',
            'telefono' => '3002223344',
            'cita' => '1',
            'concesionario_id' => $conc->id,
        ])->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', [
            'nombre' => 'Cliente Con Cita De Staff',
            'concesionario_id' => $conc->id,
        ]);
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
