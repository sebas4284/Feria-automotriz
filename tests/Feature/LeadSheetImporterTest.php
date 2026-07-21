<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Services\LeadSheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadSheetImporterTest extends TestCase
{
    use RefreshDatabase;

    private const FASE_1_HEADERS = [
        'id', 'created_time', 'ad_id', 'ad_name', 'adset_id', 'adset_name', 'campaign_id', 'campaign_name',
        'form_id', 'form_name', 'is_organic', 'platform', 'actividad_económica', 'monto_de_interés_para_aprobar',
        'full_name', 'email', 'phone_number', 'lead_status',
    ];

    private const NEW_V3_HEADERS = [
        'id', 'created_time', 'ad_id', 'ad_name', 'adset_id', 'adset_name', 'campaign_id', 'campaign_name',
        'form_id', 'form_name', 'is_organic', 'platform',
        'en_expocar_show_te_esperan_más_de_400_vehículos_y_opciones_de_financiación._¿qué_estás_buscando?',
        '¿qué_presupuesto_tienes_para_tu_próximo_vehículo?',
        '¿cuál_describe_mejor_tu_situación_laboral?',
        'full_name', 'email', 'phone_number', 'lead_status',
    ];

    public function test_fase_1_layout_maps_fields_correctly_without_motivo_busqueda(): void
    {
        $importer = app(LeadSheetImporter::class);

        $importer->import(self::FASE_1_HEADERS, [
            ['l:fase1', '2026-07-11T10:00:00-05:00', '', '', '', '', '', '', '', '', 'true', 'fb', 'independiente', '$0_-_$30_millones', 'Cliente Fase1', 'fase1@test.com', '+573000000001', 'CREATED'],
        ]);

        $lead = Lead::where('meta_lead_id', 'l:fase1')->firstOrFail();

        $this->assertSame('Cliente Fase1', $lead->full_name);
        $this->assertSame('fase1@test.com', $lead->email);
        $this->assertSame('+573000000001', $lead->phone_number);
        $this->assertSame('CREATED', $lead->meta_lead_status);
        $this->assertSame('independiente', $lead->actividad_economica);
        $this->assertNull($lead->motivo_busqueda);
    }

    public function test_new_v3_layout_with_extra_column_does_not_cross_fields(): void
    {
        $importer = app(LeadSheetImporter::class);

        $importer->import(self::NEW_V3_HEADERS, [
            ['l:newv3', '2026-07-21T10:02:14-05:00', '', '', '', '', '', '', '', '', 'false', 'fb', 'cambiar_mi_carro_actual', '$0_-_$30_millones', 'independiente', 'Caicedo Joao', 'alexbrs05@hotmail.com', '+573205369107', 'CREATED'],
        ]);

        $lead = Lead::where('meta_lead_id', 'l:newv3')->firstOrFail();

        $this->assertSame('Caicedo Joao', $lead->full_name);
        $this->assertSame('alexbrs05@hotmail.com', $lead->email);
        $this->assertSame('+573205369107', $lead->phone_number);
        $this->assertSame('CREATED', $lead->meta_lead_status);
        $this->assertSame('independiente', $lead->actividad_economica);
        $this->assertSame('cambiar_mi_carro_actual', $lead->motivo_busqueda);
    }
}
