<?php

use App\Models\Marca;
use App\Models\Vehiculo;
use App\Models\VehiculoCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        $existentes = Vehiculo::query()->pluck('marca')
            ->merge(VehiculoCatalogo::query()->pluck('marca'))
            ->map(fn ($nombre) => trim((string) $nombre))
            ->filter()
            ->unique(fn ($nombre) => strtolower($nombre));

        foreach ($existentes as $nombre) {
            Marca::firstOrCreate(['nombre' => $nombre]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marcas');
    }
};
