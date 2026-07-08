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
    Schema::create('clientes', function (Blueprint $table) {

        $table->id();

        $table->string('nombre');

        $table->string('telefono')->nullable();

        $table->string('email')->nullable();

        $table->string('ciudad')->nullable();

        $table->string('vehiculo_interes')->nullable();

        $table->decimal('presupuesto', 15, 2)->nullable();

        $table->enum('estado', [
            'Nuevo',
            'Contactado',
            'Negociacion',
            'Vendido',
            'Perdido'
        ])->default('Nuevo');

        $table->text('observaciones')->nullable();

        $table->foreignId('user_id')
              ->nullable()
              ->constrained()
              ->onDelete('set null');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
