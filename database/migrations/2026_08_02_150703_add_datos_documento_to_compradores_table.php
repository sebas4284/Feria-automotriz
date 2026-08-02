<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compradores', function (Blueprint $table) {
            $table->string('tipo_documento')->nullable()->after('identificacion');
            $table->string('lugar_expedicion')->nullable()->after('tipo_documento');
            $table->date('fecha_expedicion')->nullable()->after('lugar_expedicion');
        });
    }

    public function down(): void
    {
        Schema::table('compradores', function (Blueprint $table) {
            $table->dropColumn(['tipo_documento', 'lugar_expedicion', 'fecha_expedicion']);
        });
    }
};
