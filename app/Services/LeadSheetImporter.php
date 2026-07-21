<?php

namespace App\Services;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LeadSheetImporter
{
    /**
     * Alias de encabezados conocidos por campo. Los campos "de sistema" de Meta
     * (id, full_name, email, phone_number, lead_status, etc.) siempre usan el mismo
     * nombre de columna; solo las preguntas personalizadas del formulario cambian de
     * texto/posición entre versiones, por eso se mapea por nombre y no por índice fijo.
     */
    private const FIELD_ALIASES = [
        'meta_lead_id' => ['id'],
        'created_time' => ['created_time'],
        'ad_id' => ['ad_id'],
        'ad_name' => ['ad_name'],
        'adset_id' => ['adset_id'],
        'adset_name' => ['adset_name'],
        'campaign_id' => ['campaign_id'],
        'campaign_name' => ['campaign_name'],
        'form_id' => ['form_id'],
        'form_name' => ['form_name'],
        'is_organic' => ['is_organic'],
        'platform' => ['platform'],
        'actividad_economica' => ['actividad_económica', '¿cuál_describe_mejor_tu_situación_laboral?'],
        'monto_interes_aprobar' => ['monto_de_interés_para_aprobar', '¿qué_presupuesto_tienes_para_tu_próximo_vehículo?'],
        'motivo_busqueda' => ['en_expocar_show_te_esperan_más_de_400_vehículos_y_opciones_de_financiación._¿qué_estás_buscando?'],
        'full_name' => ['full_name'],
        'email' => ['email'],
        'phone_number' => ['phone_number'],
        'meta_lead_status' => ['lead_status'],
    ];

    public function __construct(
        private LeadAssignmentService $assignmentService,
        private LeadNotifier $notifier,
    ) {
    }

    /**
     * @param  array<int, string>  $headers  fila de encabezados de la pestaña
     * @param  array<int, array<int, string>>  $rows  filas crudas del sheet (sin encabezado)
     * @return array{created:int, updated:int, unassigned:int, skipped:int}
     */
    public function import(array $headers, array $rows): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'unassigned' => 0, 'skipped' => 0];
        $columnMap = $this->buildColumnMap($headers);

        foreach ($rows as $row) {
            $metaIdIndex = $columnMap['meta_lead_id'] ?? null;
            $metaId = $metaIdIndex !== null ? trim($row[$metaIdIndex] ?? '') : '';

            if ($metaId === '') {
                $stats['skipped']++;
                continue;
            }

            $attributes = $this->mapRow($row, $columnMap);

            $existing = Lead::where('meta_lead_id', $metaId)->first();

            if ($existing) {
                $existing->update($attributes);
                $stats['updated']++;
                continue;
            }

            $lead = Lead::create($attributes);
            $stats['created']++;

            $concesionario = $this->assignmentService->assignNext();

            if ($concesionario) {
                $lead->update([
                    'concesionario_id' => $concesionario->id,
                    'assigned_at' => now(),
                ]);

                $this->notifier->notifyConcesionario($concesionario, $lead);
            } else {
                $stats['unassigned']++;
                Log::warning("Lead {$metaId} importado sin concesionario activo disponible para asignar.");
            }
        }

        return $stats;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, int>
     */
    private function buildColumnMap(array $headers): array
    {
        $normalized = array_map(fn ($h) => trim((string) $h), $headers);

        $map = [];

        foreach (self::FIELD_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $index = array_search($alias, $normalized, true);

                if ($index !== false) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<int, string>  $row
     * @param  array<string, int>  $columnMap
     * @return array<string, mixed>
     */
    private function mapRow(array $row, array $columnMap): array
    {
        $get = fn (string $field) => isset($columnMap[$field])
            ? (trim((string) ($row[$columnMap[$field]] ?? '')) ?: null)
            : null;

        return [
            'meta_lead_id' => $get('meta_lead_id'),
            'created_time' => $this->parseDate($get('created_time')),
            'ad_id' => $get('ad_id'),
            'ad_name' => $get('ad_name'),
            'adset_id' => $get('adset_id'),
            'adset_name' => $get('adset_name'),
            'campaign_id' => $get('campaign_id'),
            'campaign_name' => $get('campaign_name'),
            'form_id' => $get('form_id'),
            'form_name' => $get('form_name'),
            'is_organic' => $this->parseBool($get('is_organic')),
            'platform' => $get('platform'),
            'actividad_economica' => $get('actividad_economica'),
            'monto_interes_aprobar' => $get('monto_interes_aprobar'),
            'motivo_busqueda' => $get('motivo_busqueda'),
            'full_name' => $get('full_name'),
            'email' => $get('email'),
            'phone_number' => $get('phone_number'),
            'meta_lead_status' => $get('meta_lead_status'),
            'raw_payload' => $row,
        ];
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseBool(?string $value): bool
    {
        return in_array(strtolower((string) $value), ['true', '1', 'yes'], true);
    }
}
