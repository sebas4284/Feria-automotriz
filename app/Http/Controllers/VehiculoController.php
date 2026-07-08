<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use App\Models\Concesionario;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehiculo::with('concesionario')->latest();

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

        return view('vehiculos.index', compact(
            'vehiculos', 'marcas', 'concesionarios',
            'precioMin', 'precioMax', 'modeloMin', 'modeloMax'
        ));
    }

    public function create()
    {
        $concesionarios = Concesionario::where('activo', 1)->get();

        return view(
            'vehiculos.create',
            compact('concesionarios')
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'concesionario_id' => 'nullable|exists:concesionarios,id',

            'placa' => 'required|string|max:20|unique:vehiculos,placa',

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
        ]);

        Vehiculo::create($request->all());

        return redirect()
            ->route('vehiculos.index')
            ->with(
                'success',
                'Vehículo creado correctamente'
            );
    }
    /**
     * Display the specified resource.
     */
    public function show(Vehiculo $vehiculo)
    {
        return view('vehiculos.show', compact('vehiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehiculo $vehiculo)
    {
        $concesionarios = Concesionario::query()
    ->where('activo', true)
    ->get();

        return view(
            'vehiculos.edit',
            compact(
                'vehiculo',
                'concesionarios'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        $request->validate([

            'concesionario_id' => 'nullable|exists:concesionarios,id',

            'placa' => 'required|string|max:20|unique:vehiculos,placa,' . $vehiculo->id,

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
        ]);

        $vehiculo->update($request->all());

        return redirect()
            ->route('vehiculos.show', $vehiculo)
            ->with(
                'success',
                'Vehículo actualizado correctamente'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->delete();

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente');
    }
}