<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('comprador_id')
                ->nullable()
                ->after('cliente_id')
                ->constrained('compradores')
                ->nullOnDelete();

            $table->foreignId('concesionario_vende_id')
                ->nullable()
                ->after('vehiculo_id')
                ->constrained('concesionarios')
                ->nullOnDelete();

            $table->foreignId('asesor_comercial_id')
                ->nullable()
                ->after('user_id')
                ->constrained('asesores_comerciales')
                ->nullOnDelete();

            $table->boolean('participa_experiencia')->default(false)->after('observaciones');

            $table->dropColumn('forma_pago');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->enum('forma_pago', ['Contado', 'Credito', 'Credito y Contado'])
                ->nullable()
                ->after('fecha_venta');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['comprador_id']);
            $table->dropForeign(['concesionario_vende_id']);
            $table->dropForeign(['asesor_comercial_id']);
            $table->dropColumn([
                'comprador_id',
                'concesionario_vende_id',
                'asesor_comercial_id',
                'participa_experiencia',
                'forma_pago',
            ]);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->string('forma_pago')->nullable()->after('fecha_venta');
        });
    }
};
