<?php

namespace Tests\Feature;

use App\Models\VehiculoCatalogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class VehiculoCatalogoImportTest extends TestCase
{
    use RefreshDatabase;

    private function crearArchivoDePrueba(array $filas): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            ['MARCA', 'LINEA', 'VERSION', 'MODELO', 'COLOR', 'CLASE DE VEHICULO', 'CC', 'COMBUSTIBLE', 'TRANSMISION', 'CIUDAD DE MATRICULA'],
            ...$filas,
        ]);

        $ruta = tempnam(sys_get_temp_dir(), 'catalogo_test_').'.xlsx';
        (new Xlsx($spreadsheet))->save($ruta);

        return $ruta;
    }

    public function test_import_deduplicates_by_ficha_tecnica(): void
    {
        $ruta = $this->crearArchivoDePrueba([
            ['Toyota', 'Corolla', 'XEi', 2020, 'Blanco', 'Sedán', 1800, 'Gasolina', 'Automática', 'Bogotá D.C.'],
            ['Toyota', 'Corolla', 'XEi', 2021, 'Negro', 'Sedán', 1800, 'Gasolina', 'Automática', 'Cali'],
            ['Toyota', 'Corolla', 'XEi', 2022, 'Gris', 'Sedán', 1800, 'Gasolina', 'Automática', 'Medellín'],
            ['Renault', 'Duster', 'EXL', 2019, 'Azul', 'SUV', 1600, 'Híbrido', 'Mecánica', 'Cali'],
        ]);

        $this->artisan('vehiculos:importar-catalogo', ['ruta' => $ruta])->assertSuccessful();

        // Las 3 filas del Corolla comparten la misma ficha técnica (año/color/ciudad no cuentan) -> 1 sola fila.
        $this->assertDatabaseCount('vehiculo_catalogo', 2);
        $this->assertDatabaseHas('vehiculo_catalogo', [
            'marca' => 'Toyota', 'linea' => 'Corolla', 'version' => 'XEi',
            'clase_vehiculo' => 'Sedán', 'cc' => 1800, 'combustible' => 'Gasolina', 'transmision' => 'Automática',
        ]);
        $this->assertDatabaseHas('vehiculo_catalogo', [
            'marca' => 'Renault', 'linea' => 'Duster', 'version' => 'EXL',
            'clase_vehiculo' => 'SUV', 'cc' => 1600, 'combustible' => 'Híbrido', 'transmision' => 'Mecánica',
        ]);

        unlink($ruta);
    }

    public function test_running_import_twice_does_not_duplicate(): void
    {
        $ruta = $this->crearArchivoDePrueba([
            ['Mazda', 'Mazda 3', 'EX', 2020, 'Rojo', 'Hatchback', 2000, 'Gasolina', 'CVT', 'Pereira'],
        ]);

        $this->artisan('vehiculos:importar-catalogo', ['ruta' => $ruta])->assertSuccessful();
        $this->artisan('vehiculos:importar-catalogo', ['ruta' => $ruta])->assertSuccessful();

        $this->assertDatabaseCount('vehiculo_catalogo', 1);

        unlink($ruta);
    }

    public function test_import_fails_gracefully_when_file_does_not_exist(): void
    {
        $this->artisan('vehiculos:importar-catalogo', ['ruta' => 'C:/no-existe/archivo.xlsx'])
            ->assertFailed();

        $this->assertDatabaseCount('vehiculo_catalogo', 0);
    }
}
