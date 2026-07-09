<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['ciudad', 'email', 'vehiculo_interes', 'presupuesto']);

            $table->boolean('cita')->default(false)->after('telefono');

            $table->foreignId('concesionario_id')
                ->nullable()
                ->after('cita')
                ->constrained('concesionarios')
                ->nullOnDelete();

            $table->enum('medio_entero', [
                'Redes sociales',
                'Referido',
                'Feria/Evento',
                'Publicidad o pagina web',
                'No se',
            ])->nullable()->after('concesionario_id');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['concesionario_id']);
            $table->dropColumn(['cita', 'concesionario_id', 'medio_entero']);

            $table->string('ciudad')->nullable();
            $table->string('email')->nullable();
            $table->string('vehiculo_interes')->nullable();
            $table->decimal('presupuesto', 15, 2)->nullable();
        });
    }
};
