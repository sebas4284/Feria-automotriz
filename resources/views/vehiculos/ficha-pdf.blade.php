<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: Helvetica, Arial, sans-serif; }
        body { color: #111; margin: 0; padding: 0; }
        .hoja { width: 100%; padding: 20px; }
        .encabezado { background: #111; padding: 10px 0; margin-bottom: 12px; text-align: center; }
        .encabezado img { height: 45px; }
        table.grid { width: 100%; border-collapse: separate; border-spacing: 6px; }
        table.grid td { vertical-align: top; width: 50%; }
        .etiqueta { font-size: 11px; text-transform: uppercase; font-weight: bold; margin-bottom: 2px; }
        .valor { background: #efefef; padding: 6px 10px; font-weight: bold; font-size: 18px; }
        .valor.placa { font-size: 32px; }
        .valor.marca { font-size: 26px; }
        .valor.grande { border: 3px solid #111; font-size: 30px; text-align: center; }
    </style>
</head>
<body>
@foreach ($vehiculos as $vehiculo)
    <div class="hoja" style="{{ $loop->last ? '' : 'page-break-after: always;' }}">
        <div class="encabezado">
            <img src="{{ public_path('images/expocarshow-logo-white.png') }}" alt="Expocar Show">
        </div>
        <table class="grid">
            <tr>
                <td>
                    <div class="etiqueta">Placa</div>
                    <div class="valor placa">{{ $vehiculo->placa ?: '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Vitrina</div>
                    <div class="valor">{{ $vehiculo->concesionario?->nombre ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="etiqueta">Marca</div>
                    <div class="valor marca">{{ $vehiculo->marca ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="etiqueta">Línea - Versión</div>
                    <div class="valor">{{ trim(($vehiculo->linea ?? '') . ' ' . ($vehiculo->version ?? '')) ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">Fecha matrícula</div>
                    <div class="valor">{{ $vehiculo->fecha_matricula ? \Carbon\Carbon::parse($vehiculo->fecha_matricula)->format('d/m/Y') : '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Ciudad matrícula</div>
                    <div class="valor">{{ $vehiculo->ciudad_matricula ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">Modelo</div>
                    <div class="valor">{{ $vehiculo->modelo ?: '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Cilindraje</div>
                    <div class="valor">{{ $vehiculo->cc !== null ? number_format($vehiculo->cc, 0, ',', '.') : '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">Combustible</div>
                    <div class="valor">{{ $vehiculo->combustible ?: '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Transmisión</div>
                    <div class="valor">{{ $vehiculo->transmision ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="etiqueta">Kilometraje</div>
                    <div class="valor">{{ $vehiculo->kilometraje !== null ? number_format($vehiculo->kilometraje, 0, ',', '.') : '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">SOAT</div>
                    <div class="valor">{{ $vehiculo->fecha_soat ? \Carbon\Carbon::parse($vehiculo->fecha_soat)->format('d/m/Y') : '—' }}</div>
                </td>
                <td>
                    <div class="etiqueta">Tecno</div>
                    <div class="valor">{{ $vehiculo->fecha_tecno ? \Carbon\Carbon::parse($vehiculo->fecha_tecno)->format('d/m/Y') : '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">PR Normal</div>
                    <div class="valor">$ {{ number_format($vehiculo->precio_normal ?? 0, 0, ',', '.') }}</div>
                </td>
                <td rowspan="2">
                    <div class="etiqueta">Accesorios</div>
                    <div class="valor">{{ $vehiculo->accesorios ?: '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="etiqueta">Bono</div>
                    <div class="valor">$ {{ number_format($vehiculo->bono_descuento ?? 0, 0, ',', '.') }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="etiqueta">Precio Feria</div>
                    <div class="valor grande">$ {{ number_format($vehiculo->precio_expocar ?? 0, 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
