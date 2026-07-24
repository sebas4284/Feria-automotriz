<?php

use App\Models\Vehiculo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('valor');
            $table->timestamps();
            $table->unique(['tipo', 'valor']);
        });

        $defaults = [
            'color' => ['Azul', 'Blanco', 'Gris', 'Negro', 'Plata', 'Rojo'],
            'combustible' => ['Gasolina', 'ACPM (Diésel)', 'Híbrido', 'Eléctrico'],
            'ciudad' => [
                'Armenia', 'Barranquilla', 'Bogotá D.C.', 'Bucaramanga', 'Cali', 'Cartagena',
                'Cúcuta', 'Ibagué', 'Manizales', 'Medellín', 'Montería', 'Neiva', 'Pasto',
                'Pereira', 'Popayán', 'Santa Marta', 'Sincelejo', 'Tunja', 'Valledupar', 'Villavicencio',
            ],
        ];

        $usados = [
            'color' => Vehiculo::query()->pluck('color'),
            'combustible' => Vehiculo::query()->pluck('combustible'),
            'ciudad' => Vehiculo::query()->pluck('ciudad_matricula'),
        ];

        foreach ($defaults as $tipo => $valores) {
            $todos = collect($valores)
                ->merge($usados[$tipo])
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->unique(fn ($v) => mb_strtolower($v));

            foreach ($todos as $valor) {
                DB::table('catalogos')->insertOrIgnore([
                    'tipo' => $tipo,
                    'valor' => $valor,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('marcas')) {
            $marcas = DB::table('marcas')->pluck('nombre');

            foreach ($marcas as $nombre) {
                DB::table('catalogos')->insertOrIgnore([
                    'tipo' => 'marca',
                    'valor' => $nombre,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::dropIfExists('marcas');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos');
    }
};
