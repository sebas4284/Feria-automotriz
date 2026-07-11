<?php

namespace Tests\Feature;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NuevoLeadAsignado;
use App\Services\LeadAssignmentService;
use App\Services\LeadNotifier;
use App\Services\LeadSheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeadNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_lead_to_asesor_notifies_that_asesors_user(): void
    {
        Notification::fake();

        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $asesorComercial = AsesorComercial::create(['cedula' => '1', 'nombre' => 'Asesor Uno', 'concesionario_id' => $conc->id]);
        $asesorUser = User::factory()->create(['rol' => 'asesor', 'asesor_comercial_id' => $asesorComercial->id]);
        $concUser = $this->concUser($conc);
        $lead = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $conc->id]);

        $this->actingAs($concUser)
            ->patch("/leads/{$lead->id}/assign-asesor", ['asesor_comercial_id' => $asesorComercial->id]);

        Notification::assertSentTo($asesorUser, NuevoLeadAsignado::class);
    }

    public function test_reassigning_lead_notifies_new_concesionarios_users(): void
    {
        Notification::fake();

        $from = Concesionario::create(['nombre' => 'Origen', 'peso_asignacion' => 1, 'activo' => true]);
        $to = Concesionario::create(['nombre' => 'Destino', 'peso_asignacion' => 1, 'activo' => true]);
        $admin = User::factory()->create(['rol' => 'admin']);
        $toUser = $this->concUser($to);
        $lead = Lead::create(['meta_lead_id' => 'l1', 'estado_gestion' => 'Nuevo', 'concesionario_id' => $from->id, 'assigned_at' => now()]);

        $this->actingAs($admin)
            ->patch("/leads/{$lead->id}/reassign", ['to_concesionario_id' => $to->id]);

        Notification::assertSentTo($toUser, NuevoLeadAsignado::class);
    }

    public function test_sheet_import_auto_assignment_notifies_concesionarios_user(): void
    {
        Notification::fake();

        $conc = Concesionario::create(['nombre' => 'A', 'peso_asignacion' => 1, 'activo' => true]);
        $concUser = $this->concUser($conc);

        $importer = app(LeadSheetImporter::class);
        $importer->import([
            ['l:import1', '2026-07-11T10:00:00-05:00', '', '', '', '', '', '', '', '', 'true', '', '', '', 'Cliente Prueba', 'test@test.com', '+573000000000', 'CREATED'],
        ]);

        Notification::assertSentTo($concUser, NuevoLeadAsignado::class);
    }

    private function concUser(Concesionario $concesionario): User
    {
        return User::factory()->create(['rol' => 'concesionario', 'concesionario_id' => $concesionario->id]);
    }
}
