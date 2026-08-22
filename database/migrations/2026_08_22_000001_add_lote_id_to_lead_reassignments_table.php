<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_reassignments', function (Blueprint $table) {
            $table->string('lote_id')->nullable()->after('motivo');
        });
    }

    public function down(): void
    {
        Schema::table('lead_reassignments', function (Blueprint $table) {
            $table->dropColumn('lote_id');
        });
    }
};
