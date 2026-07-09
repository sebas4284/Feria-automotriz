<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('estado', [
                'Nuevo',
                'Contactado',
                'Negociacion',
                'Vendido',
                'Perdido',
            ])->default('Nuevo');
        });
    }
};
