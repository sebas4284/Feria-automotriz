<?php

namespace Tests\Feature;

use App\Models\Concesionario;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ExportarVehiculosPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/exports'));
        parent::tearDown();
    }

    public function test_generates_one_combined_pdf_for_the_concesionarios_vehiculos(): void
    {
        $concesionario = Concesionario::create(['nombre' => 'Usados de Occidente']);
        Vehiculo::create(['concesionario_id' => $concesionario->id, 'placa' => 'BBB222', 'marca' => 'Kia', 'modelo' => '2021']);
        Vehiculo::create(['concesionario_id' => $concesionario->id, 'placa' => 'AAA111', 'marca' => 'Mazda', 'modelo' => '2020']);

        $this->artisan('vehiculos:exportar-pdf', ['concesionario' => 'Usados de Occidente'])
            ->assertExitCode(0);

        $archivos = File::files(storage_path('app/exports'));
        $this->assertCount(1, $archivos);
        $this->assertStringContainsString('usados-de-occidente', $archivos[0]->getFilename());
        $this->assertStringEndsWith('.pdf', $archivos[0]->getFilename());
    }

    public function test_fails_when_concesionario_does_not_exist(): void
    {
        $this->artisan('vehiculos:exportar-pdf', ['concesionario' => 'No Existe'])
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(storage_path('app/exports'));
    }

    public function test_warns_and_creates_no_file_when_concesionario_has_no_vehiculos(): void
    {
        Concesionario::create(['nombre' => 'Usados de Occidente']);

        $this->artisan('vehiculos:exportar-pdf', ['concesionario' => 'Usados de Occidente'])
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(storage_path('app/exports'));
    }
}
