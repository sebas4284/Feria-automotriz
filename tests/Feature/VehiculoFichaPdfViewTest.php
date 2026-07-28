<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiculoFichaPdfViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_key_fields_for_a_single_vehiculo(): void
    {
        $concesionario = Concesionario::create(['nombre' => 'Usados de Occidente']);

        $vehiculo = Vehiculo::create([
            'concesionario_id' => $concesionario->id,
            'placa' => 'ABC123',
            'marca' => 'Mazda',
            'modelo' => '2020',
            'linea' => 'CX-5',
            'precio_expocar' => 85000000,
        ]);

        $html = view('vehiculos.ficha-pdf', ['vehiculos' => collect([$vehiculo])])->render();

        $this->assertStringContainsString('ABC123', $html);
        $this->assertStringContainsString('Usados de Occidente', $html);
        $this->assertStringContainsString('Mazda', $html);
        $this->assertStringContainsString('CX-5', $html);
        $this->assertStringContainsString('85.000.000', $html);
    }

    public function test_adds_a_page_break_between_vehiculos_but_not_after_the_last_one(): void
    {
        $concesionario = Concesionario::create(['nombre' => 'Usados de Occidente']);

        $vehiculos = collect([
            Vehiculo::create(['concesionario_id' => $concesionario->id, 'placa' => 'AAA111', 'marca' => 'Mazda', 'modelo' => '2020']),
            Vehiculo::create(['concesionario_id' => $concesionario->id, 'placa' => 'BBB222', 'marca' => 'Kia', 'modelo' => '2021']),
        ]);

        $html = view('vehiculos.ficha-pdf', ['vehiculos' => $vehiculos])->render();

        $this->assertSame(1, substr_count($html, 'page-break-after: always'));
    }
}
