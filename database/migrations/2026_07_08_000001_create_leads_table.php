<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('meta_lead_id')->unique();
            $table->timestamp('created_time')->nullable();
            $table->string('ad_id')->nullable();
            $table->string('ad_name')->nullable();
            $table->string('adset_id')->nullable();
            $table->string('adset_name')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('form_id')->nullable();
            $table->string('form_name')->nullable();
            $table->boolean('is_organic')->default(false);
            $table->string('platform')->nullable();
            $table->string('actividad_economica')->nullable();
            $table->string('monto_interes_aprobar')->nullable();
            $table->string('email')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('meta_lead_status')->nullable();
            $table->json('raw_payload')->nullable();

            $table->foreignId('concesionario_id')
                ->nullable()
                ->constrained('concesionarios')
                ->nullOnDelete();

            $table->enum('estado_gestion', [
                'Nuevo',
                'Contactado',
                'Negociacion',
                'Vendido',
                'Perdido',
            ])->default('Nuevo');

            $table->timestamp('assigned_at')->nullable();

            $table->timestamps();

            $table->index(['estado_gestion', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
