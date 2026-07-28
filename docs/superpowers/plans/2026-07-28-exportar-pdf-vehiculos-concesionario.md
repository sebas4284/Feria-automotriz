# Exportar PDF combinado de vehículos por concesionario Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an artisan command that generates one combined PDF (one page per vehicle) with the fichas of every vehicle belonging to a given concesionario, ordered by placa.

**Architecture:** A dedicated Blade view (`vehiculos.ficha-pdf`) renders a table-based (dompdf-compatible) version of the existing vehicle ficha, looped over a collection of vehicles with a page break between each. An artisan command looks up the concesionario by exact name, loads its vehicles ordered by placa, renders that view through dompdf, and saves the result to `storage/app/exports/`.

**Tech Stack:** Laravel 12, `barryvdh/laravel-dompdf` (new dependency), PHPUnit Feature tests with `RefreshDatabase` and sqlite in-memory (existing test setup).

## Global Constraints

- No headless-browser dependency (Node/Chrome) — production hosting is shared/cPanel-style. Use `barryvdh/laravel-dompdf` (pure PHP), added as a production dependency (not `require-dev`).
- The PDF includes ALL vehicles of the concesionario regardless of `estado` (Disponible/Reservado/Vendido) — no status filtering.
- Vehicles are ordered by `placa` ascending within the PDF.
- One combined PDF, one page per vehicle (not one file per vehicle, not a browser-print flow).
- Output file path: `storage/app/exports/vehiculos-{slug-del-nombre-del-concesionario}-{YYYY-MM-DD}.pdf`, directory created if missing.
- Command matches the concesionario by **exact** `nombre` (`Concesionario::where('nombre', $nombre)->first()`), and fails clearly if not found; if found but has zero vehicles, it warns and creates no file.
- Follow existing command conventions in `app/Console/Commands/ReconciliarCupoVehiculos.php`: `protected $signature`, `protected $description`, `handle(): int` returning `self::SUCCESS` / `self::FAILURE`. No `Kernel.php` registration needed (Laravel 12 auto-discovers commands in `app/Console/Commands`).
- Tests follow the existing pattern in `tests/Feature/WacrmClientTest.php`: `Illuminate\Foundation\Testing\RefreshDatabase`, models created directly via `Model::create([...])` (no factories exist for `Concesionario`/`Vehiculo`, don't add any).

---

### Task 1: PDF-compatible ficha view

**Files:**
- Modify: `composer.json` (add `barryvdh/laravel-dompdf`)
- Create: `resources/views/vehiculos/ficha-pdf.blade.php`
- Test: `tests/Feature/VehiculoFichaPdfViewTest.php`

**Interfaces:**
- Consumes: `App\Models\Concesionario` (`nombre` fillable field, `hasMany` `vehiculos()`), `App\Models\Vehiculo` (fillable fields `concesionario_id, placa, marca, linea, version, modelo, cc, combustible, transmision, kilometraje, fecha_matricula, ciudad_matricula, fecha_soat, fecha_tecno, precio_normal, bono_descuento, precio_expocar, accesorios`, relation `concesionario()`).
- Produces: Blade view `vehiculos.ficha-pdf`, rendered with `view('vehiculos.ficha-pdf', ['vehiculos' => $vehiculosCollection])`. `$vehiculos` MUST be an `Illuminate\Support\Collection` (or `EloquentCollection`) of `Vehiculo` models — the view uses `$loop->last` from `@foreach` to control page breaks, so it cannot accept a single model. Task 2's command depends on this exact view name and parameter shape.

- [ ] **Step 1: Add the dompdf dependency**

Run: `composer require barryvdh/laravel-dompdf`

Expected: `composer.json` gains `"barryvdh/laravel-dompdf": "^3.x"` under `require` (not `require-dev`), `composer.lock` updates, and the command exits 0. The package auto-registers its service provider and `Pdf` facade via Laravel package discovery — no manual provider/facade registration or `vendor:publish` needed for this task.

- [ ] **Step 2: Write the failing test for the view**

Create `tests/Feature/VehiculoFichaPdfViewTest.php`:

```php
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
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `php artisan test --filter=VehiculoFichaPdfViewTest`
Expected: FAIL — `InvalidArgumentException: View [vehiculos.ficha-pdf] not found.`

- [ ] **Step 4: Create the view**

Create `resources/views/vehiculos/ficha-pdf.blade.php`:

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: Helvetica, Arial, sans-serif; }
        body { color: #111; margin: 0; padding: 0; }
        .hoja { width: 100%; padding: 20px; }
        .encabezado { background: #111; padding: 10px 0; margin-bottom: 12px; text-align: center; }
        .encabezado img { height: 45px; }
        table.grid { width: 100%; border-collapse: separate; border-spacing: 6px; }
        table.grid td { vertical-align: top; width: 50%; }
        .etiqueta { font-size: 11px; text-transform: uppercase; font-weight: bold; margin-bottom: 2px; }
        .valor { background: #efefef; padding: 6px 10px; font-weight: bold; font-size: 18px; }
        .valor.placa { font-size: 32px; }
        .valor.marca { font-size: 26px; }
        .valor.grande { border: 3px solid #111; font-size: 30px; text-align: center; }
    </style>
</head>
<body>
@foreach ($vehiculos as $vehiculo)
    <div class="hoja" style="{{ $loop->last ? '' : 'page-break-after: always;' }}">
        <div class="encabezado">
            <img src="{{ public_path('images/expocarshow-logo-white.png') }}" alt="Expocar Show">
        </div>
        <table class="grid">
            <tr>
                <td>
                    <div class="etiqueta">Placa</div>
                    <div class="valor placa">{{ $vehiculo->placa ?: '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Vitrina</div>
                    <div class="valor">{{ $vehiculo->concesionario?->nombre ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="etiqueta">Marca</div>
                    <div class="valor marca">{{ $vehiculo->marca ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="etiqueta">Línea - Versión</div>
                    <div class="valor">{{ trim(($vehiculo->linea ?? '') . ' ' . ($vehiculo->version ?? '')) ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">Fecha matrícula</div>
                    <div class="valor">{{ $vehiculo->fecha_matricula ? \Carbon\Carbon::parse($vehiculo->fecha_matricula)->format('d/m/Y') : '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Ciudad matrícula</div>
                    <div class="valor">{{ $vehiculo->ciudad_matricula ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">Modelo</div>
                    <div class="valor">{{ $vehiculo->modelo ?: '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Cilindraje</div>
                    <div class="valor">{{ $vehiculo->cc !== null ? number_format($vehiculo->cc, 0, ',', '.') : '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">Combustible</div>
                    <div class="valor">{{ $vehiculo->combustible ?: '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Transmisión</div>
                    <div class="valor">{{ $vehiculo->transmision ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="etiqueta">Kilometraje</div>
                    <div class="valor">{{ $vehiculo->kilometraje !== null ? number_format($vehiculo->kilometraje, 0, ',', '.') : '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">SOAT</div>
                    <div class="valor">{{ $vehiculo->fecha_soat ? \Carbon\Carbon::parse($vehiculo->fecha_soat)->format('d/m/Y') : '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Tecno</div>
                    <div class="valor">{{ $vehiculo->fecha_tecno ? \Carbon\Carbon::parse($vehiculo->fecha_tecno)->format('d/m/Y') : '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">PR Normal</div>
                    <div class="valor">$ {{ number_format($vehiculo->precio_normal ?? 0, 0, ',', '.') }}</div>
                </td>
                <td rowspan="2">
                    <div class="etiqueta">Accesorios</div>
                    <div class="valor">{{ $vehiculo->accesorios ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">Bono</div>
                    <div class="valor">$ {{ number_format($vehiculo->bono_descuento ?? 0, 0, ',', '.') }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="etiqueta">Precio Feria</div>
                    <div class="valor grande">$ {{ number_format($vehiculo->precio_expocar ?? 0, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=VehiculoFichaPdfViewTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock resources/views/vehiculos/ficha-pdf.blade.php tests/Feature/VehiculoFichaPdfViewTest.php
git commit -m "feat: add PDF-compatible ficha view for combined vehicle export"
```

---

### Task 2: `vehiculos:exportar-pdf` artisan command

**Files:**
- Create: `app/Console/Commands/ExportarVehiculosPdf.php`
- Test: `tests/Feature/ExportarVehiculosPdfTest.php`

**Interfaces:**
- Consumes: `view('vehiculos.ficha-pdf', ['vehiculos' => $vehiculos])` from Task 1 (exact name and parameter key). `Barryvdh\DomPDF\Facade\Pdf::loadView($view, $data)->setPaper($size, $orientation)->save($path)` from the Task 1 dependency.
- Produces: console command `vehiculos:exportar-pdf {concesionario}`, exit code `self::SUCCESS` (0) on success (including the "no vehicles" case) and `self::FAILURE` (1) when the concesionario doesn't exist. Writes the PDF to `storage_path('app/exports/vehiculos-{slug}-{Y-m-d}.pdf')`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/ExportarVehiculosPdfTest.php`:

```php
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=ExportarVehiculosPdfTest`
Expected: FAIL — `Command "vehiculos:exportar-pdf" is not defined.`

- [ ] **Step 3: Write the command**

Create `app/Console/Commands/ExportarVehiculosPdf.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\Concesionario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExportarVehiculosPdf extends Command
{
    protected $signature = 'vehiculos:exportar-pdf {concesionario : Nombre exacto del concesionario}';

    protected $description = 'Genera un único PDF con la ficha de cada vehículo de un concesionario, ordenados por placa.';

    public function handle(): int
    {
        $nombre = $this->argument('concesionario');

        $concesionario = Concesionario::where('nombre', $nombre)->first();

        if (! $concesionario) {
            $this->error("No existe un concesionario con el nombre \"{$nombre}\".");

            return self::FAILURE;
        }

        $vehiculos = $concesionario->vehiculos()->orderBy('placa')->get();

        if ($vehiculos->isEmpty()) {
            $this->warn("El concesionario \"{$nombre}\" no tiene vehículos registrados. No se generó ningún archivo.");

            return self::SUCCESS;
        }

        $pdf = Pdf::loadView('vehiculos.ficha-pdf', ['vehiculos' => $vehiculos])
            ->setPaper('letter', 'portrait');

        $directorio = storage_path('app/exports');

        if (! is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $rutaArchivo = $directorio . DIRECTORY_SEPARATOR . sprintf(
            'vehiculos-%s-%s.pdf',
            Str::slug($concesionario->nombre),
            now()->format('Y-m-d')
        );

        $pdf->save($rutaArchivo);

        $this->info("PDF generado con {$vehiculos->count()} vehículo(s): {$rutaArchivo}");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --filter=ExportarVehiculosPdfTest`
Expected: PASS (3 tests)

- [ ] **Step 5: Manual smoke check against the real ficha data**

Run: `php artisan tinker --execute="App\Models\Concesionario::create(['nombre' => 'Prueba Manual']); App\Models\Vehiculo::create(['concesionario_id' => App\Models\Concesionario::where('nombre','Prueba Manual')->first()->id, 'placa' => 'XYZ999', 'marca' => 'Chevrolet', 'modelo' => '2019']);"`
Then: `php artisan vehiculos:exportar-pdf "Prueba Manual"`
Expected: console prints the absolute path to a generated PDF under `storage/app/exports/`; open it and confirm the ficha renders legibly (logo, placa, marca visible, no dompdf warnings printed to console).
Cleanup: `php artisan tinker --execute="App\Models\Vehiculo::where('placa','XYZ999')->delete(); App\Models\Concesionario::where('nombre','Prueba Manual')->delete();"` and delete the generated file under `storage/app/exports/`.

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/ExportarVehiculosPdf.php tests/Feature/ExportarVehiculosPdfTest.php
git commit -m "feat: add vehiculos:exportar-pdf command for combined PDF export by concesionario"
```
