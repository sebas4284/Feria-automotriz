<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('concesionario_id')
                ->nullable()
                ->after('id')
                ->constrained('concesionarios')
                ->nullOnDelete();

            $table->enum('rol', [
                'admin',
                'concesionario'
            ])->default('concesionario');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['concesionario_id']);

            $table->dropColumn('concesionario_id');

            $table->dropColumn('rol');

        });
    }
};