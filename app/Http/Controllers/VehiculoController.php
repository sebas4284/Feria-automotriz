<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use App\Models\Concesionario;
use Illuminate\Support\Facades\Storage;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Vehiculo::with('concesionario');

        if ($idPropio = $user->concesionarioIdPropio()) {
            $query->orderByRaw('CASE WHEN concesionario_id = ? THEN 0 ELSE 1 END', [$idPropio]);
        }

        $query->latest();

        if ($request->filled('placa')) {
            $buscar = $request->placa;
            $query->where(function ($q) use ($buscar) {
                $q->where('placa', 'like', "%{$buscar}%")
                    ->orWhere('marca', 'like', "%{$buscar}%")
                    ->orWhere('linea', 'like', "%{$buscar}%")
                    ->orWhere('version', 'like', "%{$buscar}%")
                    ->orWhere('numero_llave', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('marca')) {
            $query->where('marca', $request->marca);
        }

        if ($request->filled('modelo_desde')) {
            $query->where('modelo', '>=', $request->modelo_desde);
        }

        if ($request->filled('modelo_hasta')) {
            $query->where('modelo', '<=', $request->modelo_hasta);
        }

        if ($request->filled('precio_min')) {
            $query->where('precio_expocar', '>=', $request->precio_min);
        }

        if ($request->filled('precio_max')) {
            $query->where('precio_expocar', '<=', $request->precio_max);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('ubicacion')) {
            $query->where('ubicacion', $request->ubicacion);
        }

        if ($request->filled('concesionario_id')) {
            $query->where('concesionario_id', $request->concesionario_id);
        }

        $vehiculos      = $query->get();
        $marcas         = Vehiculo::distinct()->orderBy('marca')->pluck('marca');
        $concesionarios = Concesionario::where('activo', true)->orderBy('nombre')->get();
        $precioMin      = (int) (Vehiculo::min('precio_expocar') ?? 0);
        $precioMax      = (int) (Vehiculo::max('precio_expocar') ?? 200_000_000);
        $modeloMin      = (int) (Vehiculo::min('modelo') ?? 2000);
        $modeloMax      = (int) (Vehiculo::max('modelo') ?? now()->year);

        $concesionarioCupo = null;
        $cupoUsadoActual = null;
        $concesionarioIdCupo = $user->isAdmin() ? $request->concesionario_id : $user->concesionario_id;

        if ($concesionarioIdCupo) {
            $concesionarioCupo = Concesionario::find($concesionarioIdCupo);
            $cupoUsadoActual = $this->cupoUsado($concesionarioIdCupo);
        }

        return view('vehiculos.index', compact(
            'vehiculos', 'marcas', 'concesionarios',
            'precioMin', 'precioMax', 'modeloMin', 'modeloMax',
            'concesionarioCupo', 'cupoUsadoActual'
        ));
    }

    public function create()
    {
        $this->authorize('create', Vehiculo::class);

        $concesionarios = $this->concesionariosDisponibles();

        return view(
            'vehiculos.create',
            array_merge(compact('concesionarios'), $this->catalogosDisponibles())
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Vehiculo::class);

        $request->validate([

            'concesionario_id' => 'nullable|exists:concesionarios,id',

            'placa' => 'required|string|size:6|unique:vehiculos,placa',

            'numero_llave' => 'nullable|string|max:50',

            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',

            'marca' => 'required|string|max:255',

            'linea' => 'required|string|max:255',

            'version' => 'nullable|string|max:255',

            'modelo' => 'required',

            'color' => 'nullable|string|max:255',

            'clase_vehiculo' => 'nullable|string|max:255',

            'tipo_vehiculo' => 'nullable|string|max:255',

            'cc' => 'nullable|integer',

            'combustible' => 'nullable|string|max:255',

            'transmision' => 'nullable|string|max:255',

            'kilometraje' => 'nullable|integer',

            'fecha_matricula' => 'nullable|date',

            'ciudad_matricula' => 'nullable|string|max:255',

            'fecha_soat' => 'nullable|date',

            'fecha_tecno' => 'nullable|date',

            'impuestos' => 'nullable|string|max:255',

            'accesorios' => 'nullable|string',

            'cod_fasecolda' => 'nullable|string|max:255',

            'pr_fasecolda' => 'nullable|numeric',

            'precio_normal' => 'nullable|numeric',

            'bono_descuento' => 'nullable|numeric',

            'precio_expocar' => 'nullable|numeric',

            'estado' => 'required|string',

            'ubicacion' => 'required|in:Dentro del área,Fuera del área',
        ], [
            'placa.size' => 'La placa debe tener exactamente 6 caracteres (ej: ABC123). Corrígela e intenta de nuevo.',
            'placa.unique' => 'Esta placa ya está registrada en otro vehículo.',
        ]);

        $data = $request->except('foto');
        $data['concesionario_id'] = $this->resolveConcesionarioId($request);
        $data['marca'] = mb_strtoupper($data['marca']);
        $data['precio_expocar'] = $this->calcularPrecioExpocar($data);

        $mensajeCupo = $this->ajustarUbicacionPorCupo($data);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('vehiculos', 'public');
        }

        Vehiculo::create($data);

        return redirect()
            ->route('vehiculos.index')
            ->with($mensajeCupo ? 'warning' : 'success', $mensajeCupo ?? 'Vehículo creado correctamente');
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, Vehiculo $vehiculo)
    {
        $referer = $request->headers->get('referer');
        $volverUrl = route('vehiculos.index');

        if ($referer && ! str_contains($referer, '/ficha')) {
            $volverUrl = $referer;
        }

        return view('vehiculos.show', compact('vehiculo', 'volverUrl'));
    }

    public function ficha(Vehiculo $vehiculo)
    {
        return view('vehiculos.ficha', compact('vehiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehiculo $vehiculo)
    {
        $this->authorize('update', $vehiculo);

        $concesionarios = $this->concesionariosDisponibles();

        return view(
            'vehiculos.edit',
            array_merge(compact('vehiculo', 'concesionarios'), $this->catalogosDisponibles())
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        $this->authorize('update', $vehiculo);

        $request->validate([

            'concesionario_id' => 'nullable|exists:concesionarios,id',

            'placa' => 'required|string|size:6|unique:vehiculos,placa,' . $vehiculo->id,

            'numero_llave' => 'nullable|string|max:50',

            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',

            'marca' => 'required|string|max:255',

            'linea' => 'required|string|max:255',

            'version' => 'nullable|string|max:255',

            'modelo' => 'required',

            'color' => 'nullable|string|max:255',

            'clase_vehiculo' => 'nullable|string|max:255',

            'tipo_vehiculo' => 'nullable|string|max:255',

            'cc' => 'nullable|integer',

            'combustible' => 'nullable|string|max:255',

            'transmision' => 'nullable|string|max:255',

            'kilometraje' => 'nullable|integer',

            'fecha_matricula' => 'nullable|date',

            'ciudad_matricula' => 'nullable|string|max:255',

            'fecha_soat' => 'nullable|date',

            'fecha_tecno' => 'nullable|date',

            'impuestos' => 'nullable|string|max:255',

            'accesorios' => 'nullable|string',

            'cod_fasecolda' => 'nullable|string|max:255',

            'pr_fasecolda' => 'nullable|numeric',

            'precio_normal' => 'nullable|numeric',

            'bono_descuento' => 'nullable|numeric',

            'precio_expocar' => 'nullable|numeric',

            'estado' => 'required|string',

            'ubicacion' => 'required|in:Dentro del área,Fuera del área',
        ], [
            'placa.size' => 'La placa debe tener exactamente 6 caracteres (ej: ABC123). Corrígela e intenta de nuevo.',
            'placa.unique' => 'Esta placa ya está registrada en otro vehículo.',
        ]);

        $data = $request->except('foto');
        $data['concesionario_id'] = $this->resolveConcesionarioId($request, $vehiculo);
        $data['marca'] = mb_strtoupper($data['marca']);
        $data['precio_expocar'] = $this->calcularPrecioExpocar($data);

        $mensajeCupo = $this->ajustarUbicacionPorCupo($data, $vehiculo->id);

        if ($request->hasFile('foto')) {
            if ($vehiculo->foto) {
                Storage::disk('public')->delete($vehiculo->foto);
            }

            $data['foto'] = $request->file('foto')->store('vehiculos', 'public');
        }

        $vehiculo->update($data);

        return redirect()
            ->route('vehiculos.show', $vehiculo)
            ->with($mensajeCupo ? 'warning' : 'success', $mensajeCupo ?? 'Vehículo actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        $this->authorize('delete', $vehiculo);

        if ($vehiculo->foto) {
            Storage::disk('public')->delete($vehiculo->foto);
        }

        $vehiculo->delete();

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente');
    }

    private function cupoUsado(int $concesionarioId, ?int $excluirVehiculoId = null): int
    {
        return Vehiculo::where('concesionario_id', $concesionarioId)
            ->where('ubicacion', 'Dentro del área')
            ->where('estado', '!=', 'Vendido')
            ->when($excluirVehiculoId, fn ($q) => $q->where('id', '!=', $excluirVehiculoId))
            ->count();
    }

    private function calcularPrecioExpocar(array $data): ?float
    {
        if (!isset($data['precio_normal']) || $data['precio_normal'] === null || $data['precio_normal'] === '') {
            return null;
        }

        return (float) $data['precio_normal'] - (float) ($data['bono_descuento'] ?? 0);
    }

    private function ajustarUbicacionPorCupo(array &$data, ?int $excluirVehiculoId = null): ?string
    {
        if (($data['ubicacion'] ?? null) !== 'Dentro del área' || ($data['estado'] ?? null) === 'Vendido') {
            return null;
        }

        $concesionarioId = $data['concesionario_id'] ?? null;

        if ($concesionarioId) {
            $concesionario = Concesionario::find($concesionarioId);

            if ($concesionario && $concesionario->cupo_feria !== null) {
                $usado = $this->cupoUsado($concesionarioId, $excluirVehiculoId);

                if ($usado >= $concesionario->cupo_feria) {
                    $data['ubicacion'] = 'Fuera del área';

                    return "Este concesionario ya alcanzó su cupo de feria ({$usado}/{$concesionario->cupo_feria}); el vehículo se guardó como Fuera del área.";
                }
            }
        }

        $totalGlobal = Vehiculo::where('ubicacion', 'Dentro del área')
            ->where('estado', '!=', 'Vendido')
            ->when($excluirVehiculoId, fn ($q) => $q->where('id', '!=', $excluirVehiculoId))
            ->count();

        if ($totalGlobal >= config('feria.cupo_total')) {
            $data['ubicacion'] = 'Fuera del área';

            return 'Se alcanzó el cupo total de la feria (' . config('feria.cupo_total') . '); el vehículo se guardó como Fuera del área.';
        }

        return null;
    }

    private function catalogosDisponibles(): array
    {
        return [
            'marcas' => Catalogo::tipo('marca')->orderBy('valor')->pluck('valor'),
            'ciudades' => Catalogo::tipo('ciudad')->orderBy('valor')->pluck('valor'),
            'colores' => Catalogo::tipo('color')->orderBy('valor')->pluck('valor'),
            'combustibles' => Catalogo::tipo('combustible')->orderBy('valor')->pluck('valor'),
        ];
    }

    private function concesionariosDisponibles()
    {
        $user = auth()->user();

        return $user->isAdmin()
            ? Concesionario::where('activo', true)->get()
            : Concesionario::where('id', $user->concesionarioIdPropio())->get();
    }

    private function resolveConcesionarioId(Request $request, ?Vehiculo $vehiculo = null): ?int
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $request->concesionario_id;
        }

        return $vehiculo ? $vehiculo->concesionario_id : $user->concesionarioIdPropio();
    }
}