<?php

namespace Tests\Feature;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\User;
use App\Services\UsuariosSheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuariosSheetImporterTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = ['Nombre', 'Email', 'Contraseña', 'Cedula', 'Rol', 'Concesionario'];

    public function test_asesor_row_creates_concesionario_asesor_comercial_and_user(): void
    {
        $importer = app(UsuariosSheetImporter::class);

        $importer->import('Growcars', 1001, self::HEADERS, [
            ['Luis Quintero', 'luis@example.com', '12345678', '16936701', 'Asesor', 'GROWCARS'],
        ]);

        $concesionario = Concesionario::where('nombre', 'Growcars')->firstOrFail();
        $asesor = AsesorComercial::where('cedula', '16936701')->firstOrFail();
        $user = User::where('email', 'luis@example.com')->firstOrFail();

        $this->assertSame('Growcars', $asesor->concesionario->nombre);
        $this->assertSame('asesor', $user->rol);
        $this->assertSame($asesor->id, $user->asesor_comercial_id);
        $this->assertNull($user->concesionario_id);
        $this->assertFalse($concesionario->activo);
    }

    public function test_concesionario_row_creates_user_linked_by_concesionario_id(): void
    {
        $importer = app(UsuariosSheetImporter::class);

        $importer->import('Magnata', 1002, self::HEADERS, [
            ['Laura Muñoz', 'laura@example.com', '12345678', '1007619557', 'CONCESIONARIO', 'MAGNATA'],
        ]);

        $concesionario = Concesionario::where('nombre', 'Magnata')->firstOrFail();
        $user = User::where('email', 'laura@example.com')->firstOrFail();

        $this->assertSame('concesionario', $user->rol);
        $this->assertSame($concesionario->id, $user->concesionario_id);
        $this->assertNull($user->asesor_comercial_id);
        $this->assertDatabaseCount('asesores_comerciales', 0);
    }

    public function test_cedula_with_dots_and_prefix_is_normalized_to_digits_only(): void
    {
        $importer = app(UsuariosSheetImporter::class);

        $importer->import('Autos Dicar', 1003, self::HEADERS, [
            ['Carolina Pamplona', 'carolina@example.com', '12345678', '31.570.756', 'Asesor', 'AUTOS DICAR'],
            ['Alirio Merchan', 'alirio@example.com', '12345678', 'Ppt: 854950', 'Asesor', 'AUTOS DICAR'],
        ]);

        $this->assertDatabaseHas('asesores_comerciales', ['cedula' => '31570756']);
        $this->assertDatabaseHas('asesores_comerciales', ['cedula' => '854950']);
    }

    public function test_new_user_gets_the_default_password(): void
    {
        $importer = app(UsuariosSheetImporter::class);

        $importer->import('Ezel Automotriz', 1004, self::HEADERS, [
            ['Efrain Lezama', 'efrain@example.com', '1234', '94520676', 'Concesionario', 'EZEL AUTOMOTRIZ'],
        ]);

        $user = User::where('email', 'efrain@example.com')->firstOrFail();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('expocar2026', $user->password));
    }

    public function test_rows_with_missing_email_or_unknown_role_are_skipped_and_reported(): void
    {
        $importer = app(UsuariosSheetImporter::class);

        $stats = $importer->import('Financars', 1005, self::HEADERS, [
            ['Edwin', '', '12345678', '', 'Asesor', 'FINANCARS'],
            ['Nancy Cardenas', 'nancy@example.com', '12345678', '38640764', 'Administracion', 'FINANCARS'],
        ]);

        $this->assertDatabaseCount('users', 0);
        $this->assertCount(2, $stats['omitidos']);
    }

    public function test_running_import_twice_does_not_duplicate_or_reset_password(): void
    {
        $importer = app(UsuariosSheetImporter::class);

        $importer->import('Eurocars', 1006, self::HEADERS, [
            ['Dahiana', 'dahiana@example.com', '12345678', '1005978881', 'Concesionario', 'EUROCARS'],
        ]);

        $user = User::where('email', 'dahiana@example.com')->firstOrFail();
        $user->update(['password' => 'contraseña-elegida-por-el-usuario']);

        $importer->import('Eurocars', 1006, self::HEADERS, [
            ['Dahiana', 'dahiana@example.com', '12345678', '1005978881', 'Concesionario', 'EUROCARS'],
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('concesionarios', 1);

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('contraseña-elegida-por-el-usuario', $user->password));
    }

    public function test_renombrar_el_concesionario_no_lo_desengancha_de_la_pestana(): void
    {
        $importer = app(UsuariosSheetImporter::class);

        $importer->import('MH Group', 2001, self::HEADERS, [
            ['Mauricio', 'mauricio@example.com', '12345678', '999888777', 'Concesionario', 'MH GROUP'],
        ]);

        $concesionario = Concesionario::where('sheet_tab_id', 2001)->firstOrFail();
        $concesionario->update(['nombre' => 'MH Group Renombrado']);

        // La pestaña del Sheet sigue con el nombre viejo, pero el sheet_tab_id
        // es el mismo: debe seguir enganchando al mismo concesionario, sin
        // crear un duplicado ni revertir el nombre.
        $importer->import('MH Group', 2001, self::HEADERS, [
            ['Mauricio', 'mauricio@example.com', '12345678', '999888777', 'Concesionario', 'MH GROUP'],
            ['Nuevo Asesor', 'asesor@example.com', '12345678', '111222333', 'Asesor', 'MH GROUP'],
        ]);

        $this->assertDatabaseCount('concesionarios', 1);
        $concesionario->refresh();
        $this->assertSame('MH Group Renombrado', $concesionario->nombre);

        $user = User::where('email', 'mauricio@example.com')->firstOrFail();
        $this->assertSame($concesionario->id, $user->concesionario_id);

        $asesor = AsesorComercial::where('cedula', '111222333')->firstOrFail();
        $this->assertSame($concesionario->id, $asesor->concesionario_id);
    }

    public function test_dos_pestanas_con_el_mismo_nombre_pero_distinto_sheet_tab_id_no_se_mezclan(): void
    {
        $importer = app(UsuariosSheetImporter::class);

        $importer->import('Autos Prueba', 3001, self::HEADERS, [
            ['Persona Uno', 'uno@example.com', '12345678', '111111111', 'Concesionario', 'AUTOS PRUEBA'],
        ]);

        $importer->import('Autos Prueba', 3002, self::HEADERS, [
            ['Persona Dos', 'dos@example.com', '12345678', '222222222', 'Concesionario', 'AUTOS PRUEBA'],
        ]);

        $this->assertDatabaseCount('concesionarios', 2);

        $userUno = User::where('email', 'uno@example.com')->firstOrFail();
        $userDos = User::where('email', 'dos@example.com')->firstOrFail();

        $this->assertNotSame($userUno->concesionario_id, $userDos->concesionario_id);
    }
}
