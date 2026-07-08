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
    Schema::create('vehiculos', function (Blueprint $table) {

        $table->id();

        $table->string('marca');

        $table->string('modelo');

        $table->year('anio')->nullable();

        $table->decimal('precio', 15, 2)->nullable();

        $table->integer('stock')->default(0);

        $table->string('color')->nullable();

        $table->string('transmision')->nullable();

        $table->enum('estado', [
            'Disponible',
            'Reservado',
            'Vendido'
        ])->default('Disponible');

        $table->text('descripcion')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
