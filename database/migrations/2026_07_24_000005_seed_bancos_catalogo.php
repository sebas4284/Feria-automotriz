<?php

use App\Models\Catalogo;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $bancos = [
            'Bancolombia', 'Davivienda', 'BBVA', 'Banco de Bogotá', 'Banco de Occidente',
            'Banco Popular', 'Banco Caja Social', 'Scotiabank Colpatria', 'AV Villas', 'Banco Falabella',
        ];

        foreach ($bancos as $banco) {
            Catalogo::firstOrCreate(['tipo' => 'banco', 'valor' => $banco]);
        }
    }

    public function down(): void
    {
        Catalogo::where('tipo', 'banco')->delete();
    }
};
