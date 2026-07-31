<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehiculos_eliminados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehiculo_id');
            $table->string('placa')->nullable();
            $table->string('marca')->nullable();
            $table->string('linea')->nullable();
            $table->integer('modelo')->nullable();
            $table->string('concesionario_nombre')->nullable();
            $table->decimal('precio_expocar', 15, 2)->nullable();
            $table->integer('kilometraje')->nullable();
            $table->string('estado')->nullable();
            $table->json('datos');
            $table->foreignId('eliminado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('eliminado_por_nombre')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos_eliminados');
    }
};
