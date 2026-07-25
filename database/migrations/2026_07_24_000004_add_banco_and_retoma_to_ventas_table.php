<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('banco')->nullable()->after('forma_pago');
            $table->boolean('tiene_retoma')->default(false)->after('banco');
            $table->decimal('retoma_valor', 12, 2)->nullable()->after('tiene_retoma');
            $table->string('retoma_descripcion')->nullable()->after('retoma_valor');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['banco', 'tiene_retoma', 'retoma_valor', 'retoma_descripcion']);
        });
    }
};
