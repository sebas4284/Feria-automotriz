<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {

            // Eliminar campos antiguos
            $table->dropColumn([
                'anio',
                'precio',
                'stock',
                'descripcion'
            ]);

            // Nuevos campos

            $table->foreignId('concesionario_id')
                ->nullable()
                ->after('id');

            $table->string('placa')->nullable();

            $table->string('linea')->nullable();

            $table->string('version')->nullable();

            $table->string('clase_vehiculo')->nullable();

            $table->string('tipo_vehiculo')->nullable();

            $table->integer('cc')->nullable();

            $table->string('combustible')->nullable();

            $table->integer('kilometraje')->nullable();

            $table->date('fecha_matricula')->nullable();

            $table->string('ciudad_matricula')->nullable();

            $table->date('fecha_soat')->nullable();

            $table->date('fecha_tecno')->nullable();

            $table->string('impuestos')->nullable();

            $table->text('accesorios')->nullable();

            $table->string('cod_fasecolda')->nullable();

            $table->decimal('pr_fasecolda', 15, 2)->nullable();

            $table->decimal('precio_normal', 15, 2)->nullable();

            $table->decimal('bono_descuento', 15, 2)->nullable();

            $table->decimal('precio_expocar', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        //
    }
};