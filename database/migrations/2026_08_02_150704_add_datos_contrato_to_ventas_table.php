<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('ciudad_firma')->nullable()->after('observaciones');
            $table->unsignedSmallInteger('dias_traspaso')->nullable()->after('ciudad_firma');
            $table->unsignedTinyInteger('porcentaje_gastos_vendedor')->nullable()->after('dias_traspaso');
            $table->unsignedTinyInteger('porcentaje_gastos_comprador')->nullable()->after('porcentaje_gastos_vendedor');
            $table->decimal('clausula_penal_smmlv', 6, 2)->nullable()->after('porcentaje_gastos_comprador');
            $table->string('testigo_nombre')->nullable()->after('clausula_penal_smmlv');
            $table->string('testigo_identificacion')->nullable()->after('testigo_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'ciudad_firma', 'dias_traspaso', 'porcentaje_gastos_vendedor',
                'porcentaje_gastos_comprador', 'clausula_penal_smmlv',
                'testigo_nombre', 'testigo_identificacion',
            ]);
        });
    }
};
