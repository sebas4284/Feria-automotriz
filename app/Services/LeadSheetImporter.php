<?php

namespace App\Services;

use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LeadSheetImporter
{
    public function __construct(
        private LeadAssignmentService $assignmentService,
        private LeadNotifier $notifier,
    ) {
    }

    /**
     * @param  array<int, array<int, string>>  $rows  filas crudas del sheet (sin encabezado), columnas A..R
     * @return array{created:int, updated:int, unassigned:int, skipped:int}
     */
    public function import(array $rows): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'unassigned' => 0, 'skipped' => 0];

        foreach ($rows as $row) {
            $metaId = trim($row[0] ?? '');

            if ($metaId === '') {
                $stats['skipped']++;
                continue;
            }

            $attributes = $this->mapRow($row);

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
     * @param  array<int, string>  $row
     * @return array<string, mixed>
     */
    private function mapRow(array $row): array
    {
        $get = fn (int $i) => trim($row[$i] ?? '') ?: null;

        return [
            'meta_lead_id' => $get(0),
            'created_time' => $this->parseDate($get(1)),
            'ad_id' => $get(2),
            'ad_name' => $get(3),
            'adset_id' => $get(4),
            'adset_name' => $get(5),
            'campaign_id' => $get(6),
            'campaign_name' => $get(7),
            'form_id' => $get(8),
            'form_name' => $get(9),
            'is_organic' => $this->parseBool($get(10)),
            'platform' => $get(11),
            'actividad_economica' => $get(12),
            'monto_interes_aprobar' => $get(13),
            'full_name' => $get(14),
            'email' => $get(15),
            'phone_number' => $get(16),
            'meta_lead_status' => $get(17),
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
