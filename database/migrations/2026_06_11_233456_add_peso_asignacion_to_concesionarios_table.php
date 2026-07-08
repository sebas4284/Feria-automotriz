<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concesionarios', function (Blueprint $table) {

            $table->integer('peso_asignacion')
                  ->default(1)
                  ->after('responsable');

        });
    }

    public function down(): void
    {
        Schema::table('concesionarios', function (Blueprint $table) {

            $table->dropColumn('peso_asignacion');

        });
    }
};