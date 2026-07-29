<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->unsignedInteger('veces_procesado')->default(0)->after('veces_asignado');
        });

        // Turnos ya existentes (de hoy) arrancan el contador de rondas desde
        // sus asignaciones reales ya hechas, para no perder cuenta a mitad de día.
        DB::table('turnos')->update(['veces_procesado' => DB::raw('veces_asignado')]);
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropColumn('veces_procesado');
        });
    }
};
