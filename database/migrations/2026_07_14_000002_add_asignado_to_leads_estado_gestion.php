<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {

            $table->enum('estado_gestion', [
                'Nuevo',
                'Asignado',
                'Contactado',
                'Negociacion',
                'Vendido',
                'Perdido',
            ])->default('Nuevo')->change();

        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {

            $table->enum('estado_gestion', [
                'Nuevo',
                'Contactado',
                'Negociacion',
                'Vendido',
                'Perdido',
            ])->default('Nuevo')->change();

        });
    }
};
