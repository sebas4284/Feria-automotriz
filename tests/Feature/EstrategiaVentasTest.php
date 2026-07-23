<?php

namespace Tests\Feature;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstrategiaVentasTest extends TestCase
{
    use RefreshDatabase;

    private function makeLead(array $overrides = []): Lead
    {
        return Lead::create(array_merge([
            'meta_lead_id' => 'l:' . uniqid(),
            'estado_gestion' => 'Nuevo',
        ], $overrides));
    }

    public function test_only_admin_can_access_estrategia(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concesionarioUser = User::factory()->create(['rol' => 'concesionario', 'concesionario_id' => $conc->id]);
        $asesor = User::factory()->create(['rol' => 'asesor']);
        $staff = User::factory()->create(['rol' => 'staff']);

        $this->actingAs($admin)->get('/estrategia')->assertOk();
        $this->actingAs($concesionarioUser)->get('/estrategia')->assertForbidden();
        $this->actingAs($asesor)->get('/estrategia')->assertForbidden();
        $this->actingAs($staff)->get('/estrategia')->assertForbidden();
    }

    public function test_embudo_counts_leads_by_estado_gestion(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->makeLead(['estado_gestion' => 'Nuevo']);
        $this->makeLead(['estado_gestion' => 'Nuevo']);
        $this->makeLead(['estado_gestion' => 'Vendido']);
        $this->makeLead(['estado_gestion' => 'Perdido']);

        $response = $this->actingAs($admin)->get('/estrategia');

        $response->assertOk();
        $response->assertViewHas('embudo', function ($embudo) {
            return $embudo['Nuevo'] === 2
                && $embudo['Vendido'] === 1
                && $embudo['Perdido'] === 1
                && $embudo['Asignado'] === 0;
        });
        $response->assertViewHas('totalLeads', 4);
        $response->assertViewHas('tasaConversionGlobal', 25.0);
    }

    public function test_conversion_por_canal_calculates_correct_percentage(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->makeLead(['platform' => 'Facebook', 'estado_gestion' => 'Vendido']);
        $this->makeLead(['platform' => 'Facebook', 'estado_gestion' => 'Vendido']);
        $this->makeLead(['platform' => 'Facebook', 'estado_gestion' => 'Nuevo']);
        $this->makeLead(['platform' => 'Facebook', 'estado_gestion' => 'Perdido']);

        $response = $this->actingAs($admin)->get('/estrategia');

        $response->assertOk();
        $response->assertViewHas('conversionPorCanal', function ($conversion) {
            $facebook = $conversion->firstWhere('etiqueta', 'Facebook');

            return $facebook->total === 4 && $facebook->vendidos === 2 && $facebook->tasa === 50.0;
        });
    }

    public function test_leads_vencidos_only_includes_leads_matching_scope(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        config(['leads.staleness_hours' => 48]);

        $vencido = $this->makeLead([
            'estado_gestion' => 'Nuevo',
            'assigned_at' => now()->subHours(72),
        ]);
        $this->makeLead([
            'estado_gestion' => 'Nuevo',
            'assigned_at' => now()->subHours(2),
        ]);
        $this->makeLead(['estado_gestion' => 'Vendido', 'assigned_at' => now()->subHours(72)]);

        $response = $this->actingAs($admin)->get('/estrategia');

        $response->assertOk();
        $response->assertViewHas('leadsVencidos', function ($leadsVencidos) use ($vencido) {
            return $leadsVencidos->count() === 1 && $leadsVencidos->first()->id === $vencido->id;
        });
    }

    public function test_antiguedad_promedio_excludes_terminal_states(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->makeLead(['estado_gestion' => 'Nuevo', 'created_time' => now()->subDays(10)]);
        $this->makeLead(['estado_gestion' => 'Vendido', 'created_time' => now()->subDays(100)]);
        $this->makeLead(['estado_gestion' => 'Perdido', 'created_time' => now()->subDays(100)]);

        $response = $this->actingAs($admin)->get('/estrategia');

        $response->assertOk();
        $response->assertViewHas('antiguedad', function ($antiguedad) {
            return $antiguedad['global'] == 10.0;
        });
    }

    public function test_conversion_por_asesor_only_considers_leads_with_asesor_assigned(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesor = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);

        $this->makeLead(['asesor_comercial_id' => $asesor->id, 'estado_gestion' => 'Vendido']);
        $this->makeLead(['asesor_comercial_id' => $asesor->id, 'estado_gestion' => 'Nuevo']);
        $this->makeLead(['estado_gestion' => 'Vendido']); // sin asesor, no debe contar

        $response = $this->actingAs($admin)->get('/estrategia');

        $response->assertOk();
        $response->assertViewHas('conversionPorAsesor', function ($conversion) {
            $fila = $conversion->firstWhere('etiqueta', 'Asesor Uno');

            return $conversion->count() === 1 && $fila->total === 2 && $fila->vendidos === 1;
        });
    }
}
