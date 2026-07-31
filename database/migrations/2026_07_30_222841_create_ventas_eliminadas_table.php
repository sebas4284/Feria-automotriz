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
        Schema::create('ventas_eliminadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id');
            $table->string('vehiculo_placa')->nullable();
            $table->string('vehiculo_marca')->nullable();
            $table->string('vehiculo_modelo')->nullable();
            $table->string('comprador_nombre')->nullable();
            $table->string('concesionario_vende_nombre')->nullable();
            $table->string('asesor_nombre')->nullable();
            $table->decimal('valor', 15, 2)->nullable();
            $table->date('fecha_venta')->nullable();
            $table->text('motivo');
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
        Schema::dropIfExists('ventas_eliminadas');
    }
};
