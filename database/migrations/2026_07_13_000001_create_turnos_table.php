<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('concesionario_id')
                ->constrained('concesionarios')
                ->cascadeOnDelete();

            $table->date('fecha');
            $table->timestamp('llegada_at');
            $table->timestamp('ultima_asignacion_at')->nullable();
            $table->unsignedInteger('veces_asignado')->default(0);

            $table->timestamps();

            $table->unique(['concesionario_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
