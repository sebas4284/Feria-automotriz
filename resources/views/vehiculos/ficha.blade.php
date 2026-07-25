<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha {{ $vehiculo->placa }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #e5e5e5;
            margin: 0;
            padding: 24px;
        }
        .hoja {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #111;
        }
        .encabezado {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #111;
            padding: 10px 16px;
        }
        .encabezado img {
            height: 40px;
        }
        .encabezado .placa {
            color: #fff;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .celda {
            border: 1px solid #111;
            padding: 8px 12px;
            min-height: 44px;
        }
        .celda.ancha {
            grid-column: 1 / -1;
        }
        .etiqueta {
            font-size: 10px;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
        }
        .valor {
            font-size: 15px;
            font-weight: bold;
            margin-top: 2px;
            word-break: break-word;
        }
        .valor.grande {
            font-size: 18px;
            color: #0a7a2f;
        }
        .acciones {
            max-width: 720px;
            margin: 16px auto 0;
            text-align: right;
        }
        .acciones button {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .hoja { border: none; }
            .acciones { display: none; }
        }
    </style>
</head>
<body>

    <div class="hoja">

        <div class="encabezado">
            <img src="{{ asset('images/expocarshow-logo-white.png') }}" alt="Expocar Show">
            <div class="placa">{{ $vehiculo->placa }}</div>
        </div>

        <div class="grid">

            <div class="celda">
                <div class="etiqueta">Marca</div>
                <div class="valor">{{ $vehiculo->marca ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Línea</div>
                <div class="valor">{{ $vehiculo->linea ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Versión</div>
                <div class="valor">{{ $vehiculo->version ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Modelo</div>
                <div class="valor">{{ $vehiculo->modelo ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Color</div>
                <div class="valor">{{ $vehiculo->color ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Kilometraje</div>
                <div class="valor">{{ $vehiculo->kilometraje !== null ? number_format($vehiculo->kilometraje, 0, ',', '.') . ' km' : '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Combustible</div>
                <div class="valor">{{ $vehiculo->combustible ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Transmisión</div>
                <div class="valor">{{ $vehiculo->transmision ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Clase de vehículo</div>
                <div class="valor">{{ $vehiculo->clase_vehiculo ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Tipo de vehículo</div>
                <div class="valor">{{ $vehiculo->tipo_vehiculo ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">CC</div>
                <div class="valor">{{ $vehiculo->cc ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Número de llave</div>
                <div class="valor">{{ $vehiculo->numero_llave ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">SOAT</div>
                <div class="valor">{{ $vehiculo->fecha_soat ? \Carbon\Carbon::parse($vehiculo->fecha_soat)->format('d/m/Y') : '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Tecnomecánica</div>
                <div class="valor">{{ $vehiculo->fecha_tecno ? \Carbon\Carbon::parse($vehiculo->fecha_tecno)->format('d/m/Y') : '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Fecha matrícula</div>
                <div class="valor">{{ $vehiculo->fecha_matricula ? \Carbon\Carbon::parse($vehiculo->fecha_matricula)->format('d/m/Y') : '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Ciudad matrícula</div>
                <div class="valor">{{ $vehiculo->ciudad_matricula ?: '—' }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Precio Normal</div>
                <div class="valor">$ {{ number_format($vehiculo->precio_normal ?? 0, 0, ',', '.') }}</div>
            </div>

            <div class="celda">
                <div class="etiqueta">Precio Feria</div>
                <div class="valor grande">$ {{ number_format($vehiculo->precio_expocar ?? 0, 0, ',', '.') }}</div>
            </div>

            <div class="celda ancha">
                <div class="etiqueta">Concesionario</div>
                <div class="valor">{{ $vehiculo->concesionario?->nombre ?: '—' }}</div>
            </div>

            <div class="celda ancha">
                <div class="etiqueta">Accesorios</div>
                <div class="valor">{{ $vehiculo->accesorios ?: '—' }}</div>
            </div>

        </div>

    </div>

    <div class="acciones">
        <button onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

</body>
</html>
