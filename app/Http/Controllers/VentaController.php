<?php

namespace App\Http\Controllers;

use App\Models\AsesorComercial;
use App\Models\Cliente;
use App\Models\Comprador;
use App\Models\Concesionario;
use App\Models\Venta;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $ventas = Venta::with(['cliente', 'vehiculo', 'comprador', 'concesionarioVende', 'asesorComercial'])
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $this->authorize('create', Venta::class);

        return view('ventas.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Venta::class);

        $validated = $request->validate($this->rules());

        $vehiculo = Vehiculo::findOrFail($validated['vehiculo_id']);

        if ($vehiculo->estado !== 'Disponible') {
            return back()
                ->withInput()
                ->with('error', 'Este vehículo ya no está disponible para la venta.');
        }

        $comprador = $this->updateOrCreateComprador($validated);

        $venta = Venta::create([
            'cliente_id' => $validated['cliente_id'] ?? null,
            'comprador_id' => $comprador->id,
            'vehiculo_id' => $vehiculo->id,
            'concesionario_vende_id' => $validated['concesionario_vende_id'],
            'user_id' => auth()->id(),
            'asesor_comercial_id' => $validated['asesor_comercial_id'],
            'valor' => $validated['valor'],
            'fecha_venta' => $validated['fecha_venta'],
            'forma_pago' => $validated['forma_pago'],
            'observaciones' => $validated['observaciones'] ?? null,
            'participa_experiencia' => $request->boolean('participa_experiencia'),
        ]);

        $vehiculo->update(['estado' => 'Vendido']);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta registrada correctamente');
    }

    public function show(Venta $venta)
    {
        $this->authorize('view', $venta);

        $venta->load(['cliente', 'vehiculo.concesionario', 'comprador', 'concesionarioVende', 'asesorComercial', 'usuario']);

        return view('ventas.show', compact('venta'));
    }

    public function edit(Venta $venta)
    {
        $this->authorize('update', $venta);

        $venta->load(['comprador', 'vehiculo.concesionario']);

        return view('ventas.edit', $this->formData($venta) + ['venta' => $venta]);
    }

    public function update(Request $request, Venta $venta)
    {
        $this->authorize('update', $venta);

        $validated = $request->validate($this->rules());

        $nuevoVehiculoId = (int) $validated['vehiculo_id'];

        if ($nuevoVehiculoId !== $venta->vehiculo_id) {
            $nuevoVehiculo = Vehiculo::findOrFail($nuevoVehiculoId);

            if ($nuevoVehiculo->estado !== 'Disponible') {
                return back()
                    ->withInput()
                    ->with('error', 'El vehículo seleccionado ya no está disponible.');
            }
        }

        $comprador = $this->updateOrCreateComprador($validated);

        DB::transaction(function () use ($validated, $venta, $nuevoVehiculoId, $comprador, $request) {
            if ($nuevoVehiculoId !== $venta->vehiculo_id) {
                $venta->vehiculo?->update(['estado' => 'Disponible']);
                Vehiculo::find($nuevoVehiculoId)?->update(['estado' => 'Vendido']);
            }

            $venta->update([
                'cliente_id' => $validated['cliente_id'] ?? null,
                'comprador_id' => $comprador->id,
                'vehiculo_id' => $nuevoVehiculoId,
                'concesionario_vende_id' => $validated['concesionario_vende_id'],
                'asesor_comercial_id' => $validated['asesor_comercial_id'],
                'valor' => $validated['valor'],
                'fecha_venta' => $validated['fecha_venta'],
                'forma_pago' => $validated['forma_pago'],
                'observaciones' => $validated['observaciones'] ?? null,
                'participa_experiencia' => $request->boolean('participa_experiencia'),
            ]);
        });

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta actualizada correctamente');
    }

    public function destroy(Venta $venta)
    {
        $this->authorize('delete', $venta);

        $venta->vehiculo?->update(['estado' => 'Disponible']);
        $venta->delete();

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta eliminada correctamente');
    }

    private function rules(): array
    {
        return [
            'comprador_identificacion' => 'required|string|max:50',
            'comprador_nombre' => 'required|string|max:255',
            'comprador_telefono' => 'nullable|string|max:30',
            'comprador_direccion' => 'nullable|string|max:255',
            'comprador_correo' => 'nullable|email|max:255',
            'cliente_id' => 'nullable|exists:clientes,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'concesionario_vende_id' => 'required|exists:concesionarios,id',
            'asesor_comercial_id' => 'required|exists:asesores_comerciales,id',
            'valor' => 'required|numeric|min:0',
            'fecha_venta' => 'required|date',
            'forma_pago' => 'required|in:Contado,Credito,Credito y Contado',
            'observaciones' => 'nullable|string',
        ];
    }

    private function updateOrCreateComprador(array $validated): Comprador
    {
        return Comprador::updateOrCreate(
            ['identificacion' => $validated['comprador_identificacion']],
            [
                'nombre' => $validated['comprador_nombre'],
                'telefono' => $validated['comprador_telefono'] ?? null,
                'direccion' => $validated['comprador_direccion'] ?? null,
                'correo' => $validated['comprador_correo'] ?? null,
            ]
        );
    }

    private function formData(?Venta $venta = null): array
    {
        $clientes = Cliente::orderBy('nombre')->get();

        $vehiculosQuery = Vehiculo::with('concesionario')->where('estado', 'Disponible');
        if ($venta) {
            $vehiculosQuery->orWhere('id', $venta->vehiculo_id);
        }
        $vehiculos = $vehiculosQuery->orderBy('marca')->get();

        $concesionarios = Concesionario::orderBy('nombre')->get();
        $asesores = AsesorComercial::orderBy('nombre')->get();
        $compradores = Comprador::select('id', 'identificacion', 'nombre', 'telefono', 'direccion', 'correo')->get();

        $vehiculosJson = $vehiculos->map(fn (Vehiculo $v) => [
            'id' => $v->id,
            'placa' => $v->placa,
            'marca' => $v->marca,
            'modelo' => $v->modelo,
            'precio_expocar' => $v->precio_expocar,
            'concesionario_id' => $v->concesionario_id,
            'concesionario_nombre' => $v->concesionario->nombre ?? 'Sin concesionario',
        ]);

        $asesoresJson = $asesores->map(fn (AsesorComercial $a) => [
            'id' => $a->id,
            'cedula' => $a->cedula,
            'nombre' => $a->nombre,
            'concesionario_id' => $a->concesionario_id,
        ]);

        return compact(
            'clientes',
            'vehiculos',
            'concesionarios',
            'asesores',
            'vehiculosJson',
            'asesoresJson',
            'compradores'
        );
    }
}
