<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('catalogos')->where('tipo', 'marca')->get() as $catalogo) {
            DB::table('catalogos')
                ->where('id', $catalogo->id)
                ->update(['valor' => mb_strtoupper($catalogo->valor)]);
        }

        foreach (DB::table('vehiculos')->whereNotNull('marca')->get() as $vehiculo) {
            DB::table('vehiculos')
                ->where('id', $vehiculo->id)
                ->update(['marca' => mb_strtoupper($vehiculo->marca)]);
        }
    }

    public function down(): void
    {
        // No-op: no se guarda el mayus/minus original, no es reversible con seguridad.
    }
};
