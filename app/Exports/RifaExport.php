<?php

namespace App\Exports;

use App\Models\Venta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RifaExport implements FromCollection, WithHeadings, WithMapping
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
            'Boleta', 'Comprador', 'Cédula', 'Teléfono', 'Vehículo',
            'Vendedor', 'Concesionario', 'Detalle', 'Fecha',
        ];
    }

    public function map($venta): array
    {
        /** @var Venta $venta */
        return [
            $venta->boleta,
            $venta->comprador->nombre ?? '',
            $venta->comprador->identificacion ?? '',
            $venta->comprador->telefono ?? '',
            trim(($venta->vehiculo->marca ?? '') . ' ' . ($venta->vehiculo->modelo ?? '')),
            $venta->asesorComercial->nombre ?? '',
            $venta->concesionarioVende->nombre ?? '',
            $venta->detalle_experiencia ?? '',
            $venta->fecha_venta?->format('d/m/Y'),
        ];
    }
}
