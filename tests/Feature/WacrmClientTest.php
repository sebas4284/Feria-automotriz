<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Services\WacrmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WacrmClientTest extends TestCase
{
    use RefreshDatabase;

    private function makeLead(array $overrides = []): Lead
    {
        return Lead::create(array_merge([
            'meta_lead_id' => 'l:' . uniqid(),
            'full_name' => 'Cliente Prueba',
            'phone_number' => '+573001234567',
            'estado_gestion' => 'Nuevo',
        ], $overrides));
    }

    public function test_does_nothing_when_disabled(): void
    {
        config(['wacrm.enabled' => false, 'wacrm.url' => 'https://wacrm.test', 'wacrm.api_key' => 'key']);
        Http::fake();

        app(WacrmClient::class)->syncContact($this->makeLead());

        Http::assertNothingSent();
    }

    public function test_does_nothing_without_url_or_key(): void
    {
        config(['wacrm.enabled' => true, 'wacrm.url' => null, 'wacrm.api_key' => null]);
        Http::fake();

        app(WacrmClient::class)->syncContact($this->makeLead());

        Http::assertNothingSent();
    }

    public function test_pushes_contact_with_normalized_phone_and_name(): void
    {
        config(['wacrm.enabled' => true, 'wacrm.url' => 'https://wacrm.test', 'wacrm.api_key' => 'secret-key']);
        Http::fake(['*' => Http::response(['data' => ['id' => 'abc']], 201)]);

        app(WacrmClient::class)->syncContact($this->makeLead(['full_name' => 'Juan Pérez', 'phone_number' => '+57 300 123 4567']));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://wacrm.test/api/v1/contacts'
                && $request->hasHeader('Authorization', 'Bearer secret-key')
                && $request['phone'] === '+573001234567'
                && $request['name'] === 'Juan Pérez';
        });
    }

    public function test_skips_lead_without_phone(): void
    {
        config(['wacrm.enabled' => true, 'wacrm.url' => 'https://wacrm.test', 'wacrm.api_key' => 'key']);
        Http::fake();

        app(WacrmClient::class)->syncContact($this->makeLead(['phone_number' => null]));

        Http::assertNothingSent();
    }

    public function test_network_failure_does_not_throw(): void
    {
        config(['wacrm.enabled' => true, 'wacrm.url' => 'https://wacrm.test', 'wacrm.api_key' => 'key']);
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('timeout');
        });

        app(WacrmClient::class)->syncContact($this->makeLead());

        $this->assertTrue(true);
    }
}
