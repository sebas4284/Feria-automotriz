<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('medio_entero')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('medio_entero', [
                'Redes sociales',
                'Referido',
                'Feria/Evento',
                'Publicidad o pagina web',
                'No se',
            ])->nullable()->change();
        });
    }
};
