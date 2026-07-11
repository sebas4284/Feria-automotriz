<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {

            $table->foreignId('asesor_comercial_id')
                ->nullable()
                ->after('concesionario_id')
                ->constrained('asesores_comerciales')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {

            $table->dropForeign(['asesor_comercial_id']);

            $table->dropColumn('asesor_comercial_id');

        });
    }
};
