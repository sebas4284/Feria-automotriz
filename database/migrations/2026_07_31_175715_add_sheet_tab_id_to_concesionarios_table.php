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
        Schema::table('concesionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('sheet_tab_id')->nullable()->unique()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('concesionarios', function (Blueprint $table) {
            $table->dropColumn('sheet_tab_id');
        });
    }
};
