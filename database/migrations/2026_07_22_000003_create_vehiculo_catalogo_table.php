<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculo_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('marca');
            $table->string('linea');
            $table->string('version');
            $table->string('clase_vehiculo');
            $table->unsignedInteger('cc')->nullable();
            $table->string('combustible');
            $table->string('transmision');
            $table->timestamps();

            $table->unique(
                ['marca', 'linea', 'version', 'clase_vehiculo', 'cc', 'combustible', 'transmision'],
                'vehiculo_catalogo_ficha_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_catalogo');
    }
};
