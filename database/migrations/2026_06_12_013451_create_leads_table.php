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
    Schema::create('leads', function (Blueprint $table) {

        $table->id();

        $table->string('nombre');

        $table->string('telefono');

        $table->string('email')->nullable();

        $table->string('ciudad')->nullable();

        $table->string('vehiculo_interes')->nullable();

        $table->enum('estado', [
            'Nuevo',
            'Contactado',
            'Interesado',
            'Cita',
            'Negociacion',
            'Vendido',
            'Perdido',
            'Reasignado'
        ])->default('Nuevo');

        $table->foreignId('concesionario_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        $table->text('observacion')->nullable();

        $table->timestamp('fecha_asignacion')
            ->nullable();

        $table->timestamp('ultima_gestion')
            ->nullable();

        $table->integer('reasignaciones')
            ->default(0);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
