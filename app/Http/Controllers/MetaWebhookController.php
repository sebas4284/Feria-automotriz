<?php

namespace App\Http\Controllers;

use App\Models\Concesionario;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    // GET /webhook/meta — Meta verifica que el endpoint existe
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.meta.verify_token')) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    // POST /webhook/meta — Meta notifica que llegó un nuevo lead
    public function handle(Request $request)
    {
        if (!$this->verifySignature($request->header('X-Hub-Signature-256'), $request->getContent())) {
            return response('Unauthorized', 401);
        }

        $payload = $request->json();

        if ($payload->get('object') !== 'page') {
            return response('EVENT_RECEIVED', 200);
        }

        foreach ($payload->get('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'leadgen') {
                    continue;
                }

                $leadgenId = $change['value']['leadgen_id'] ?? null;

                if (!$leadgenId) {
                    continue;
                }

                // Evitar duplicados
                if (Lead::where('meta_lead_id', $leadgenId)->exists()) {
                    continue;
                }

                $this->processLead($leadgenId);
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function processLead(string $leadgenId): void
    {
        $response = Http::get("https://graph.facebook.com/v20.0/{$leadgenId}", [
            'fields'       => 'field_data,created_time',
            'access_token' => config('services.meta.page_access_token'),
        ]);

        if (!$response->successful()) {
            Log::error('Meta: error al obtener lead', [
                'leadgen_id' => $leadgenId,
                'response'   => $response->body(),
            ]);
            return;
        }

        $fields = collect($response->json('field_data', []));

        $nombre = $this->field($fields, ['full_name', 'nombre', 'first_name']) ?? 'Sin nombre';
        $telefono = $this->field($fields, ['phone_number', 'telefono', 'phone']) ?? '';
        $email = $this->field($fields, ['email', 'correo']);
        $ciudad = $this->field($fields, ['city', 'ciudad']);
        $vehiculoInteres = $this->field($fields, ['vehiculo_interes', 'vehicle_interest', 'modelo_interes']);

        $concesionarioAsignado = $this->asignarConcesionario();

        Lead::create([
            'meta_lead_id'    => $leadgenId,
            'nombre'          => $nombre,
            'telefono'        => $telefono,
            'email'           => $email,
            'ciudad'          => $ciudad,
            'vehiculo_interes' => $vehiculoInteres,
            'estado'          => 'Nuevo',
            'fuente'          => 'Meta',
            'concesionario_id' => $concesionarioAsignado?->id,
            'fecha_asignacion' => now(),
            'reasignaciones'  => 0,
        ]);
    }

    private function field(\Illuminate\Support\Collection $fields, array $names): ?string
    {
        foreach ($names as $name) {
            $match = $fields->firstWhere('name', $name);
            if ($match && !empty($match['values'][0])) {
                return $match['values'][0];
            }
        }
        return null;
    }

    private function asignarConcesionario(): ?\App\Models\Concesionario
    {
        $concesionarios = Concesionario::where('activo', true)->get();

        $pool = [];
        foreach ($concesionarios as $c) {
            for ($i = 0; $i < $c->peso_asignacion; $i++) {
                $pool[] = $c;
            }
        }

        return !empty($pool) ? $pool[array_rand($pool)] : null;
    }

    private function verifySignature(?string $signature, string $payload): bool
    {
        if (!$signature) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $payload, config('services.meta.app_secret'));

        return hash_equals($expected, $signature);
    }
}
