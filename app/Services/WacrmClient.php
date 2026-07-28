<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WacrmClient
{
    public function syncContact(Lead $lead): void
    {
        if (! config('wacrm.enabled') || ! config('wacrm.url') || ! config('wacrm.api_key')) {
            return;
        }

        $phone = preg_replace('/\D/', '', $lead->phone_number ?? '');

        if ($phone === '') {
            Log::info("WacrmClient: lead {$lead->meta_lead_id} sin teléfono, no se sincroniza.");

            return;
        }

        try {
            $response = Http::withToken(config('wacrm.api_key'))
                ->timeout(5)
                ->post(rtrim(config('wacrm.url'), '/') . '/api/v1/contacts', [
                    'phone' => '+' . $phone,
                    'name' => $lead->full_name,
                    'tags' => ['Feria Expocar'],
                ]);

            if (! $response->successful()) {
                Log::warning("WacrmClient: fallo al sincronizar lead {$lead->meta_lead_id} (HTTP {$response->status()}): {$response->body()}");
            }
        } catch (\Throwable $e) {
            Log::warning("WacrmClient: excepción al sincronizar lead {$lead->meta_lead_id}: {$e->getMessage()}");
        }
    }
}
