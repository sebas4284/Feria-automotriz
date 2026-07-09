<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesores_comerciales', function (Blueprint $table) {
            $table->id();
            $table->string('cedula')->unique();
            $table->string('nombre');
            $table->string('telefono')->nullable();

            $table->foreignId('concesionario_id')
                ->nullable()
                ->constrained('concesionarios')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesores_comerciales');
    }
};
