<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concesionarios', function (Blueprint $table) {
            $table->unsignedInteger('cupo_feria')->nullable()->after('peso_asignacion');
        });
    }

    public function down(): void
    {
        Schema::table('concesionarios', function (Blueprint $table) {
            $table->dropColumn('cupo_feria');
        });
    }
};
