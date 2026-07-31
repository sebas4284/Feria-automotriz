<?php

namespace App\Http\Controllers;

use App\Models\AsesorComercial;
use App\Models\Catalogo;
use App\Models\Cliente;
use App\Models\Comprador;
use App\Models\Concesionario;
use App\Models\Venta;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        $venta = DB::transaction(function () use ($validated, $request) {
            $vehiculo = Vehiculo::lockForUpdate()->findOrFail($validated['vehiculo_id']);

            if ($vehiculo->estado !== 'Disponible') {
                return null;
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
                'detalle_experiencia' => $validated['detalle_experiencia'] ?? null,
            ] + $this->pagoExtra($validated, $request));

            $vehiculo->update(['estado' => 'Vendido']);

            return $venta;
        });

        if (! $venta) {
            return back()
                ->withInput()
                ->with('error', 'Este vehículo ya no está disponible para la venta.');
        }

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

        $bloqueado = DB::transaction(function () use ($validated, $venta, $nuevoVehiculoId, $request) {
            if ($nuevoVehiculoId !== $venta->vehiculo_id) {
                $nuevoVehiculo = Vehiculo::lockForUpdate()->findOrFail($nuevoVehiculoId);

                if ($nuevoVehiculo->estado !== 'Disponible') {
                    return true;
                }

                $venta->vehiculo?->update(['estado' => 'Disponible']);
                $nuevoVehiculo->update(['estado' => 'Vendido']);
            }

            $comprador = $this->updateOrCreateComprador($validated);

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
                'detalle_experiencia' => $validated['detalle_experiencia'] ?? null,
            ] + $this->pagoExtra($validated, $request));

            return false;
        });

        if ($bloqueado) {
            return back()
                ->withInput()
                ->with('error', 'El vehículo seleccionado ya no está disponible.');
        }

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
            'valor' => 'required|numeric|gt:0',
            'fecha_venta' => 'required|date|before_or_equal:today',
            'forma_pago' => 'required|in:Contado,Credito,Credito y Contado',
            'banco' => 'nullable|string|max:255|required_if:forma_pago,Credito|required_if:forma_pago,Credito y Contado',
            'tiene_retoma' => 'nullable|boolean',
            'retoma_valor' => 'nullable|numeric|min:0|lte:valor|required_if:tiene_retoma,1',
            'retoma_descripcion' => 'nullable|string|max:255|required_if:tiene_retoma,1',
            'observaciones' => 'nullable|string',
            'detalle_experiencia' => 'nullable|string|max:255',
        ];
    }

    /**
     * Datos de banco/retoma a guardar, limpiando los que no aplican
     * según la forma de pago y si tiene o no retoma.
     */
    private function pagoExtra(array $validated, Request $request): array
    {
        $requiereBanco = in_array($validated['forma_pago'], ['Credito', 'Credito y Contado'], true);
        $tieneRetoma = $request->boolean('tiene_retoma');

        return [
            'banco' => $requiereBanco ? $validated['banco'] : null,
            'tiene_retoma' => $tieneRetoma,
            'retoma_valor' => $tieneRetoma ? $validated['retoma_valor'] : null,
            'retoma_descripcion' => $tieneRetoma ? $validated['retoma_descripcion'] : null,
        ];
    }

    private function updateOrCreateComprador(array $validated): Comprador
    {
        $existente = Comprador::where('identificacion', $validated['comprador_identificacion'])->first();

        if ($existente && mb_strtolower(trim($existente->nombre)) !== mb_strtolower(trim($validated['comprador_nombre']))) {
            throw ValidationException::withMessages([
                'comprador_identificacion' => "Ya existe un comprador con esa identificación registrado como \"{$existente->nombre}\". Verifica el número de identificación o el nombre.",
            ]);
        }

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
        $bancos = Catalogo::tipo('banco')->orderBy('valor')->pluck('valor');
        $detallesExperiencia = Catalogo::tipo('detalle_experiencia')->orderBy('valor')->pluck('valor');

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
            'compradores',
            'bancos',
            'detallesExperiencia'
        );
    }
}
