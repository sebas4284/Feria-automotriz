<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_reassignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete();

            $table->foreignId('from_concesionario_id')
                ->nullable()
                ->constrained('concesionarios')
                ->nullOnDelete();

            $table->foreignId('to_concesionario_id')
                ->nullable()
                ->constrained('concesionarios')
                ->nullOnDelete();

            $table->foreignId('reassigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('motivo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_reassignments');
    }
};
