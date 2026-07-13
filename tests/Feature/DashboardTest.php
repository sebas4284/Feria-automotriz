<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $rol, ?Concesionario $concesionario = null): User
    {
        return User::factory()->create([
            'rol' => $rol,
            'concesionario_id' => $concesionario?->id,
        ]);
    }

    public function test_dashboard_loads_for_every_role(): void
    {
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);

        foreach (['admin', 'concesionario', 'asesor', 'staff'] as $rol) {
            $user = $this->makeUser($rol, $conc);
            $this->actingAs($user)->get('/dashboard')->assertOk();
        }
    }

    public function test_pipeline_only_counts_leads_visible_to_the_concesionario(): void
    {
        $concA = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concB = Concesionario::create(['nombre' => 'B', 'peso_asignacion' => 1, 'activo' => true]);
        $userA = $this->makeUser('concesionario', $concA);

        Lead::create(['meta_lead_id' => 'la', 'full_name' => 'Lead Concesionario A', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concA->id]);
        Lead::create(['meta_lead_id' => 'lb', 'full_name' => 'Lead Concesionario B', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $concB->id]);

        $response = $this->actingAs($userA)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Lead Concesionario A');
        $response->assertDontSee('Lead Concesionario B');
    }

    public function test_staff_does_not_see_the_pipeline(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Pipeline de Ventas');
    }
}
