<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['cliente', 'vehiculo'])
            ->latest()
            ->get();

        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $vehiculos = Vehiculo::orderBy('marca')->get();

        return view('ventas.create', compact(
            'clientes',
            'vehiculos'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required',
            'vehiculo_id' => 'required',
            'valor' => 'required|numeric',
            'fecha_venta' => 'required|date',
        ]);

        $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);

        if ($vehiculo->stock <= 0) {
            return back()->with(
                'error',
                'Este vehículo no tiene stock disponible.'
            );
        }

        Venta::create([
            'cliente_id' => $request->cliente_id,
            'vehiculo_id' => $request->vehiculo_id,
            'user_id' => auth()->id(),
            'valor' => $request->valor,
            'fecha_venta' => $request->fecha_venta,
            'forma_pago' => $request->forma_pago,
            'observaciones' => $request->observaciones,
        ]);

        // Cambiar cliente a vendido
        Cliente::where('id', $request->cliente_id)
            ->update([
                'estado' => 'Vendido'
            ]);

        // Descontar stock
        $vehiculo->decrement('stock');

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta registrada correctamente');
    }
}