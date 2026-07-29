<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CatalogoController extends Controller
{
    private const TIPOS = [
        'marca' => 'Marcas',
        'ciudad' => 'Ciudades',
        'color' => 'Colores',
        'combustible' => 'Combustibles',
        'banco' => 'Bancos',
        'detalle_experiencia' => 'Detalles de Experiencia',
    ];

    public function index(string $tipo)
    {
        $this->validarTipo($tipo);

        $items = Catalogo::tipo($tipo)->orderBy('valor')->get();

        return view('catalogos.index', [
            'tipo' => $tipo,
            'tipos' => self::TIPOS,
            'etiqueta' => self::TIPOS[$tipo],
            'items' => $items,
        ]);
    }

    public function store(Request $request, string $tipo)
    {
        $this->validarTipo($tipo);

        $request->validate([
            'valor' => 'required|string|max:255',
        ]);

        $valor = trim($request->valor);

        if ($tipo === 'marca') {
            $valor = mb_strtoupper($valor);
        }

        $existe = Catalogo::tipo($tipo)->get()->contains(
            fn ($item) => mb_strtolower($item->valor) === mb_strtolower($valor)
        );

        if ($existe) {
            throw ValidationException::withMessages(['valor' => 'Ese valor ya existe.']);
        }

        Catalogo::create(['tipo' => $tipo, 'valor' => $valor]);

        return redirect()
            ->route('catalogos.index', $tipo)
            ->with('success', self::TIPOS[$tipo] . ': agregado correctamente');
    }

    public function destroy(Catalogo $catalogo)
    {
        $tipo = $catalogo->tipo;

        $catalogo->delete();

        return redirect()
            ->route('catalogos.index', $tipo)
            ->with('success', 'Eliminado correctamente');
    }

    private function validarTipo(string $tipo): void
    {
        abort_unless(array_key_exists($tipo, self::TIPOS), 404);
    }
}
