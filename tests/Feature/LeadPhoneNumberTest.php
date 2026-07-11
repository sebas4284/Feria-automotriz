<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadPhoneNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_p_prefix_is_stripped_on_save(): void
    {
        $lead = Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Nuevo',
            'phone_number' => 'p:+573157582270',
        ]);

        $this->assertEquals('+573157582270', $lead->phone_number);
        $this->assertEquals('+573157582270', $lead->fresh()->phone_number);
    }

    public function test_p_prefix_is_stripped_on_read_for_legacy_rows(): void
    {
        $lead = Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Nuevo',
        ]);

        // Simula un registro viejo que ya tenía el prefijo "p:" guardado
        // directamente en la base de datos, sin pasar por el mutator.
        \Illuminate\Support\Facades\DB::table('leads')->where('id', $lead->id)
            ->update(['phone_number' => 'p:+573001112233']);

        $this->assertEquals('+573001112233', $lead->fresh()->phone_number);
    }

    public function test_whatsapp_url_uses_digits_only(): void
    {
        $lead = Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Nuevo',
            'phone_number' => 'p:+57 315 758 2270',
        ]);

        $this->assertEquals('https://wa.me/573157582270', $lead->whatsapp_url);
    }

    public function test_whatsapp_url_is_null_without_phone(): void
    {
        $lead = Lead::create([
            'meta_lead_id' => 'l1',
            'estado_gestion' => 'Nuevo',
        ]);

        $this->assertNull($lead->whatsapp_url);
    }
}
