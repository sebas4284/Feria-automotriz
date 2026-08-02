<?php

namespace App\Exports;

use App\Models\Venta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VentasExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $ventas)
    {
    }

    public function collection()
    {
        return $this->ventas;
    }

    public function headings(): array
    {
        return [
            'Comprador', 'Identificación', 'Placa', 'Marca', 'Modelo',
            'Dueño', 'Vendedor', 'Asesor', 'Forma de pago', 'Valor', 'Fecha',
        ];
    }

    public function map($venta): array
    {
        /** @var Venta $venta */
        return [
            $venta->comprador->nombre ?? '',
            $venta->comprador->identificacion ?? '',
            $venta->vehiculo->placa ?? '',
            $venta->vehiculo->marca ?? '',
            $venta->vehiculo->modelo ?? '',
            $venta->vehiculo->concesionario->nombre ?? '',
            $venta->concesionarioVende->nombre ?? '',
            $venta->asesorComercial->nombre ?? '',
            $venta->forma_pago,
            $venta->valor,
            $venta->fecha_venta?->format('d/m/Y'),
        ];
    }
}
