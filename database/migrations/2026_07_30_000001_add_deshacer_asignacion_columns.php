<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->timestamp('ultima_asignacion_at_previa')->nullable()->after('ultima_asignacion_at');
            $table->boolean('tiene_asignacion_deshacible')->default(false)->after('ultima_asignacion_at_previa');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('oculto_en_turnos')->default(false)->after('concesionario_id');
        });
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropColumn(['ultima_asignacion_at_previa', 'tiene_asignacion_deshacible']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('oculto_en_turnos');
        });
    }
};
