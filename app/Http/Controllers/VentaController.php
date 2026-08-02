<?php

namespace App\Http\Controllers;

use App\Models\AsesorComercial;
use App\Models\Catalogo;
use App\Models\Cliente;
use App\Models\Comprador;
use App\Models\Concesionario;
use App\Models\Venta;
use App\Models\VentaEliminada;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $ventas = Venta::with(['cliente', 'vehiculo.concesionario', 'comprador', 'concesionarioVende', 'asesorComercial'])
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
        $validated = $request->validate($this->rules());

        $vehiculoSeleccionado = Vehiculo::findOrFail($validated['vehiculo_id']);
        $this->authorize('create', [Venta::class, $vehiculoSeleccionado]);

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

    public function destroy(Request $request, Venta $venta)
    {
        $this->authorize('delete', $venta);

        $validated = $request->validate(['motivo' => 'required|string|max:500']);

        VentaEliminada::create([
            'venta_id' => $venta->id,
            'vehiculo_placa' => $venta->vehiculo->placa ?? null,
            'vehiculo_marca' => $venta->vehiculo->marca ?? null,
            'vehiculo_modelo' => $venta->vehiculo->modelo ?? null,
            'comprador_nombre' => $venta->comprador->nombre ?? null,
            'concesionario_vende_nombre' => $venta->concesionarioVende->nombre ?? null,
            'asesor_nombre' => $venta->asesorComercial->nombre ?? null,
            'valor' => $venta->valor,
            'fecha_venta' => $venta->fecha_venta,
            'motivo' => $validated['motivo'],
            'datos' => $venta->toArray(),
            'eliminado_por' => auth()->id(),
            'eliminado_por_nombre' => auth()->user()->name,
        ]);

        $venta->vehiculo?->update(['estado' => 'Disponible']);
        $venta->delete();

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta eliminada correctamente');
    }

    public function eliminadas()
    {
        $eliminadas = VentaEliminada::latest()->get();

        return view('ventas.eliminadas', compact('eliminadas'));
    }

    public function contrato(Venta $venta)
    {
        $this->authorize('update', $venta);

        $venta->load(['comprador', 'vehiculo.concesionario', 'concesionarioVende']);

        return view('ventas.contrato', compact('venta'));
    }

    public function actualizarContrato(Request $request, Venta $venta)
    {
        $this->authorize('update', $venta);

        $validated = $request->validate([
            'comprador_tipo_documento' => 'required|in:CC,CE,NIT,Pasaporte',
            'comprador_lugar_expedicion' => 'nullable|string|max:255',
            'comprador_fecha_expedicion' => 'nullable|date|before_or_equal:today',
            'ciudad_firma' => 'nullable|string|max:255',
            'dias_traspaso' => 'nullable|integer|min:0|max:365',
            'porcentaje_gastos_vendedor' => 'nullable|integer|min:0|max:100',
            'porcentaje_gastos_comprador' => 'nullable|integer|min:0|max:100',
            'clausula_penal_smmlv' => 'nullable|numeric|min:0',
            'testigo_nombre' => 'nullable|string|max:255',
            'testigo_identificacion' => 'nullable|string|max:50',
        ]);

        $venta->comprador->update([
            'tipo_documento' => $validated['comprador_tipo_documento'],
            'lugar_expedicion' => $validated['comprador_lugar_expedicion'] ?? null,
            'fecha_expedicion' => $validated['comprador_fecha_expedicion'] ?? null,
        ]);

        $venta->update(collect($validated)->except([
            'comprador_tipo_documento', 'comprador_lugar_expedicion', 'comprador_fecha_expedicion',
        ])->toArray());

        return redirect()
            ->route('ventas.contrato', $venta)
            ->with('success', 'Datos del contrato guardados');
    }

    public function contratoPdf(Venta $venta)
    {
        $this->authorize('update', $venta);

        $venta->load(['comprador', 'vehiculo.concesionario', 'concesionarioVende']);

        return view('ventas.contrato-pdf', compact('venta'));
    }

    public function analisis(Request $request)
    {
        $query = $this->aplicarFiltroFecha(Venta::query(), $request);

        $ventas = (clone $query)->with('vehiculo:id,concesionario_id')->get(['id', 'vehiculo_id', 'concesionario_vende_id', 'valor']);

        $ventasCruzadas = (clone $query)
            ->with(['vehiculo.concesionario', 'concesionarioVende', 'comprador'])
            ->get()
            ->filter(fn ($v) => $v->vehiculo && $v->vehiculo->concesionario_id !== $v->concesionario_vende_id)
            ->values();

        return view('ventas.analisis', $this->calcularResumenVentas($query) + [
            'porAsesor' => $this->rankingAsesores(null, $query),
            'rankingConcesionarios' => $this->rankingPorConcesionario($ventas),
            'ventasCruzadas' => $ventasCruzadas,
            'esGlobal' => true,
            'fechaDesde' => $request->fecha_desde,
            'fechaHasta' => $request->fecha_hasta,
        ]);
    }

    public function analisisPropio(Request $request)
    {
        $user = $request->user();

        $query = $this->aplicarFiltroFecha(Venta::visibleTo($user), $request);

        $ventasCruzadas = (clone $query)
            ->with(['vehiculo.concesionario', 'concesionarioVende', 'comprador'])
            ->get()
            ->filter(fn ($v) => $v->vehiculo && $v->vehiculo->concesionario_id !== $v->concesionario_vende_id)
            ->values();

        return view('ventas.analisis', $this->calcularResumenVentas($query) + [
            'porAsesor' => $this->rankingAsesores($user->concesionarioIdPropio(), $query),
            'rankingConcesionarios' => null,
            'ventasCruzadas' => $ventasCruzadas,
            'esGlobal' => false,
            'fechaDesde' => $request->fecha_desde,
            'fechaHasta' => $request->fecha_hasta,
        ]);
    }

    private function aplicarFiltroFecha($query, Request $request)
    {
        return $query
            ->when($request->filled('fecha_desde'), fn ($q) => $q->whereDate('fecha_venta', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->whereDate('fecha_venta', '<=', $request->fecha_hasta));
    }

    private function calcularResumenVentas($query): array
    {
        $totalVentas = (clone $query)->count();
        $valorTotal = (clone $query)->sum('valor');
        $promedioVenta = $totalVentas > 0 ? $valorTotal / $totalVentas : 0;

        $ventasPorDia = (clone $query)
            ->selectRaw('fecha_venta, COUNT(*) as total_ventas, SUM(valor) as total_valor')
            ->groupBy('fecha_venta')
            ->orderBy('fecha_venta')
            ->get();

        $porFormaPagoRaw = (clone $query)
            ->selectRaw('forma_pago, COUNT(*) as total_ventas, SUM(valor) as total_valor')
            ->groupBy('forma_pago')
            ->get();

        // Se agrupa "Credito" y "Credito y Contado" en PHP (no con SQL
        // CASE/IF) para que funcione igual en SQLite (tests) y MySQL (prod).
        $porFormaPago = $porFormaPagoRaw
            ->groupBy(fn ($item) => $item->forma_pago === 'Contado' ? 'Contado' : 'Crédito')
            ->map(fn ($grupo, $etiqueta) => (object) [
                'forma_pago' => $etiqueta,
                'total_ventas' => $grupo->sum('total_ventas'),
                'total_valor' => $grupo->sum('total_valor'),
            ])->values();

        $porBanco = (clone $query)
            ->whereNotNull('banco')
            ->selectRaw('banco, COUNT(*) as total_ventas, SUM(valor) as total_valor')
            ->groupBy('banco')
            ->orderByDesc('total_ventas')
            ->get();

        return compact('totalVentas', 'valorTotal', 'promedioVenta', 'ventasPorDia', 'porFormaPago', 'porBanco');
    }

    private function rankingAsesores(?int $concesionarioId = null, $query = null)
    {
        $query = (clone ($query ?? Venta::query()))
            ->selectRaw('asesor_comercial_id, COUNT(*) as total_ventas, SUM(valor) as total_valor')
            ->with('asesorComercial.concesionario')
            ->groupBy('asesor_comercial_id')
            ->orderByDesc('total_ventas');

        if ($concesionarioId) {
            $query->whereHas('asesorComercial', fn ($q) => $q->where('concesionario_id', $concesionarioId));
        }

        return $query->get();
    }

    /**
     * Ranking por concesionario: "vendidas (como vendedor)" = autos de OTRO
     * concesionario que este vendió. "De su inventario" = sus propios autos
     * vendidos, sea por él mismo o por otro — coincide con "Vendidos" en
     * Vehículos.
     */
    private function rankingPorConcesionario($ventas)
    {
        $porConcesionario = [];
        foreach ($ventas as $venta) {
            $vendedorId = $venta->concesionario_vende_id;
            $duenoId = $venta->vehiculo?->concesionario_id;
            $esPropia = $duenoId && $duenoId === $vendedorId;

            if (! $esPropia) {
                $porConcesionario[$vendedorId] ??= ['vendidas_ventas' => 0, 'vendidas_valor' => 0, 'inventario_ventas' => 0, 'inventario_valor' => 0];
                $porConcesionario[$vendedorId]['vendidas_ventas']++;
                $porConcesionario[$vendedorId]['vendidas_valor'] += $venta->valor;
            }

            if ($duenoId) {
                $porConcesionario[$duenoId] ??= ['vendidas_ventas' => 0, 'vendidas_valor' => 0, 'inventario_ventas' => 0, 'inventario_valor' => 0];
                $porConcesionario[$duenoId]['inventario_ventas']++;
                $porConcesionario[$duenoId]['inventario_valor'] += $venta->valor;
            }
        }

        $nombres = Concesionario::whereIn('id', array_keys($porConcesionario))->pluck('nombre', 'id');

        return collect($porConcesionario)->map(fn ($d, $id) => [
            'nombre' => $nombres[$id] ?? 'Sin concesionario',
            'vendidas_ventas' => $d['vendidas_ventas'],
            'vendidas_valor' => $d['vendidas_valor'],
            'inventario_ventas' => $d['inventario_ventas'],
            'inventario_valor' => $d['inventario_valor'],
            'total_ventas' => $d['vendidas_ventas'] + $d['inventario_ventas'],
            'total_valor' => $d['vendidas_valor'] + $d['inventario_valor'],
        ])->sortByDesc('total_ventas')->values();
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
        $user = auth()->user();

        $clientes = Cliente::orderBy('nombre')->get();

        $vehiculosQuery = Vehiculo::with('concesionario')->where(function ($q) use ($user) {
            $q->where('estado', 'Disponible');

            if (! $user->isAdmin()) {
                $q->where('concesionario_id', $user->concesionarioIdPropio());
            }
        });

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
