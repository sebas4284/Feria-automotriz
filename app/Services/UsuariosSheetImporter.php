<?php

namespace App\Services;

use App\Models\AsesorComercial;
use App\Models\Concesionario;
use App\Models\User;

class UsuariosSheetImporter
{
    private const PASSWORD_POR_DEFECTO = 'expocar2026';

    /**
     * Ubica la fila de encabezado real dentro de un rango leído crudo:
     * la fila donde la primera celda es "Nombre" (algunas pestañas traen
     * el nombre comercial del concesionario y una fila vacía antes).
     *
     * @param  array<int, array<int, string>>  $rows
     */
    public function buscarFilaEncabezado(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            if (isset($row[0]) && strtolower(trim($row[0])) === 'nombre') {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string>>  $rows  filas de datos (sin encabezado)
     * @return array{concesionarios_creados:int, usuarios_creados:int, usuarios_actualizados:int, omitidos:array<int, string>}
     */
    public function import(string $concesionarioNombre, int $sheetTabId, array $headers, array $rows): array
    {
        $stats = ['concesionarios_creados' => 0, 'usuarios_creados' => 0, 'usuarios_actualizados' => 0, 'omitidos' => []];

        $columnMap = $this->buildColumnMap($headers);
        $nombreConcesionario = trim($concesionarioNombre);

        $concesionario = Concesionario::where('sheet_tab_id', $sheetTabId)->first();

        if (! $concesionario) {
            // Primera vez que se ve esta pestaña: intenta enganchar por nombre
            // (compatibilidad con concesionarios ya existentes) antes de crear uno nuevo.
            // Una vez enganchado por sheet_tab_id, renombrar el concesionario en la
            // app ya no lo desengancha en futuras corridas.
            $concesionario = Concesionario::where('nombre', $nombreConcesionario)->whereNull('sheet_tab_id')->first();

            if ($concesionario) {
                $concesionario->update(['sheet_tab_id' => $sheetTabId]);
            } else {
                $concesionario = Concesionario::create([
                    'nombre' => $nombreConcesionario,
                    'activo' => false,
                    'sheet_tab_id' => $sheetTabId,
                ]);
                $stats['concesionarios_creados']++;
            }
        }

        foreach ($rows as $row) {
            $get = fn (string $field) => isset($columnMap[$field])
                ? trim((string) ($row[$columnMap[$field]] ?? ''))
                : '';

            $nombre = $get('nombre');
            $email = strtolower($get('email'));
            $rol = strtolower($get('rol'));
            $cedula = preg_replace('/\D/', '', $get('cedula'));

            if ($nombre === '' || $email === '') {
                $stats['omitidos'][] = "{$nombreConcesionario}: fila sin nombre o email (\"{$nombre}\")";
                continue;
            }

            if (! in_array($rol, ['concesionario', 'asesor'], true)) {
                $stats['omitidos'][] = "{$nombreConcesionario}: {$nombre} <{$email}> tiene un rol no reconocido (\"{$rol}\")";
                continue;
            }

            $asesorComercialId = null;

            if ($rol === 'asesor') {
                if ($cedula === '') {
                    $stats['omitidos'][] = "{$nombreConcesionario}: {$nombre} <{$email}> es asesor sin cédula";
                    continue;
                }

                $asesorComercial = AsesorComercial::updateOrCreate(
                    ['cedula' => $cedula],
                    ['nombre' => $nombre, 'concesionario_id' => $concesionario->id]
                );

                $asesorComercialId = $asesorComercial->id;
            }

            $existente = User::where('email', $email)->first();

            $datos = [
                'name' => $nombre,
                'rol' => $rol,
                'concesionario_id' => $rol === 'concesionario' ? $concesionario->id : null,
                'asesor_comercial_id' => $asesorComercialId,
            ];

            if ($existente) {
                $existente->update($datos);
                $stats['usuarios_actualizados']++;
            } else {
                User::create($datos + ['email' => $email, 'password' => self::PASSWORD_POR_DEFECTO]);
                $stats['usuarios_creados']++;
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
        $aliases = [
            'nombre' => ['nombre'],
            'email' => ['email'],
            'cedula' => ['cedula', 'cédula'],
            'rol' => ['rol'],
        ];

        $normalized = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

        $map = [];

        foreach ($aliases as $field => $opciones) {
            foreach ($opciones as $alias) {
                $index = array_search($alias, $normalized, true);

                if ($index !== false) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        return $map;
    }
}
