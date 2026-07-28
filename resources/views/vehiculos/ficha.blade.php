<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha {{ $vehiculo->placa }}</title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        body {
            font-family: "Arial Narrow", Arial, Helvetica, sans-serif;
            color: #111;
            background: #e5e5e5;
            margin: 0;
            padding: 24px;
        }

        .hoja {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }

        .encabezado {
            background: #111;
            border-radius: 6px;
            padding: 8px 0;
            margin-bottom: 12px;
            height: 70px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .encabezado img {
            height: 55px;
            object-fit: contain;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
            grid-template-areas:
                "placa       vitrina"
                "marca       marca"
                "linea       linea"
                "fecha       ciudad"
                "modelo      cilindraje"
                "combustible transmision"
                "kilometraje kilometraje"
                "soat        tecno"
                "prnormal    accesorios"
                "bono        accesorios"
                "prferia     prferia";
        }

        .celda {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .etiqueta {
            font-size: 11px;
            text-transform: uppercase;
            color: #111;
            font-weight: 800;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .valor {
            flex: 1;
            background: #efefef;
            border-radius: 4px;
            padding: 4px 10px;
            font-weight: 900;
            word-break: break-word;
            display: flex;
            align-items: center;
            font-size: 22px; /* Aumentado globalmente */
        }

        /* --- Tamaños específicos para destacar contenido --- */
        .placa .valor       { font-size: 45px; letter-spacing: 1px; }
        .vitrina .valor     { font-size: 26px; line-height: 1.1; }
        .marca .valor       { font-size: 34px; }
        .linea .valor       { font-size: 26px; }
        .fecha .valor,
        .ciudad .valor      { font-size: 22px; }
        .modelo .valor      { font-size: 30px; }
        .cilindraje .valor  { font-size: 26px; }
        .combustible .valor,
        .transmision .valor { font-size: 22px; }
        .kilometraje .valor { font-size: 32px; }
        .soat .valor,
        .tecno .valor       { font-size: 22px; }
        .prnormal .valor    { background: #fff; font-size: 24px; }
        .bono .valor        { background: #fff; font-size: 24px; }

        .valor.grande {
            background: #fff;
            color: #111;
            border: 3px solid #111;
            border-radius: 8px;
            font-size: 40px;
            font-weight: 900;
            justify-content: center;
        }

        .placa { grid-area: placa; }
        .vitrina { grid-area: vitrina; }
        .marca { grid-area: marca; }
        .linea { grid-area: linea; }
        .fecha { grid-area: fecha; }
        .ciudad { grid-area: ciudad; }
        .modelo { grid-area: modelo; }
        .cilindraje { grid-area: cilindraje; }
        .combustible { grid-area: combustible; }
        .transmision { grid-area: transmision; }
        .kilometraje { grid-area: kilometraje; }
        .soat { grid-area: soat; }
        .tecno { grid-area: tecno; }
        .prnormal { grid-area: prnormal; }
        .bono { grid-area: bono; }
        .prferia { grid-area: prferia; }
        .accesorios { grid-area: accesorios; }

        .accesorios .valor {
            align-items: flex-start;
            font-size: 15px;
            line-height: 1.35;
            font-weight: 700;
            padding: 8px 10px;
        }

        .acciones {
            max-width: 800px;
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

        /* --- CONFIGURACIÓN DE IMPRESIÓN --- */
        @media print {
            @page {
                size: letter portrait;
                margin: 4mm;
            }

            html, body {
                height: 100%;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: hidden;
            }

            .hoja {
                width: 100% !important;
                max-width: 100% !important;
                height: 100vh !important;
                padding: 0 !important;
                margin: 0 !important;
                display: flex;
                flex-direction: column;
            }

            .encabezado {
                height: 60px;
                margin-bottom: 6px;
                flex-shrink: 0;
            }

            .grid {
                flex: 1;
                height: 100%;
                gap: 5px 8px;
                /* Filas distribuidas dinámicamente */
                grid-template-rows: 1.1fr 1.1fr 1fr 1fr 1.1fr 1fr 1.1fr 1fr 1fr 1fr 1.4fr;
            }

            .acciones {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="hoja">

        <div class="encabezado">
            <img src="{{ asset('images/expocarshow-logo-white.png') }}" alt="Expocar Show">
        </div>

        <div class="grid">

            <div class="celda placa">
                <div class="etiqueta">Placa</div>
                <div class="valor">{{ $vehiculo->placa ?: '—' }}</div>
            </div>

            <div class="celda vitrina">
                <div class="etiqueta">Vitrina</div>
                <div class="valor">{{ $vehiculo->concesionario?->nombre ?: '—' }}</div>
            </div>

            <div class="celda marca">
                <div class="etiqueta">Marca</div>
                <div class="valor">{{ $vehiculo->marca ?: '—' }}</div>
            </div>

            <div class="celda linea">
                <div class="etiqueta">Línea - Versión</div>
                <div class="valor">{{ trim(($vehiculo->linea ?? '') . ' ' . ($vehiculo->version ?? '')) ?: '—' }}</div>
            </div>

            <div class="celda fecha">
                <div class="etiqueta">Fecha matrícula</div>
                <div class="valor">{{ $vehiculo->fecha_matricula ? \Carbon\Carbon::parse($vehiculo->fecha_matricula)->format('d/m/Y') : '—' }}</div>
            </div>

            <div class="celda ciudad">
                <div class="etiqueta">Ciudad matrícula</div>
                <div class="valor">{{ $vehiculo->ciudad_matricula ?: '—' }}</div>
            </div>

            <div class="celda modelo">
                <div class="etiqueta">Modelo</div>
                <div class="valor">{{ $vehiculo->modelo ?: '—' }}</div>
            </div>

            <div class="celda cilindraje">
                <div class="etiqueta">Cilindraje</div>
                <div class="valor">{{ $vehiculo->cc !== null ? number_format($vehiculo->cc, 0, ',', '.') : '—' }}</div>
            </div>

            <div class="celda combustible">
                <div class="etiqueta">Combustible</div>
                <div class="valor">{{ $vehiculo->combustible ?: '—' }}</div>
            </div>

            <div class="celda transmision">
                <div class="etiqueta">Transmisión</div>
                <div class="valor">{{ $vehiculo->transmision ?: '—' }}</div>
            </div>

            <div class="celda kilometraje">
                <div class="etiqueta">Kilometraje</div>
                <div class="valor">{{ $vehiculo->kilometraje !== null ? number_format($vehiculo->kilometraje, 0, ',', '.') : '—' }}</div>
            </div>

            <div class="celda soat">
                <div class="etiqueta">SOAT</div>
                <div class="valor">{{ $vehiculo->fecha_soat ? \Carbon\Carbon::parse($vehiculo->fecha_soat)->format('d/m/Y') : '—' }}</div>
            </div>

            <div class="celda tecno">
                <div class="etiqueta">Tecno</div>
                <div class="valor">{{ $vehiculo->fecha_tecno ? \Carbon\Carbon::parse($vehiculo->fecha_tecno)->format('d/m/Y') : '—' }}</div>
            </div>

            <div class="celda prnormal">
                <div class="etiqueta">PR Normal</div>
                <div class="valor">$ {{ number_format($vehiculo->precio_normal ?? 0, 0, ',', '.') }}</div>
            </div>

            <div class="celda bono">
                <div class="etiqueta">Bono</div>
                <div class="valor">$ {{ number_format($vehiculo->bono_descuento ?? 0, 0, ',', '.') }}</div>
            </div>

            <div class="celda prferia">
                <div class="etiqueta">PR Feria</div>
                <div class="valor grande">$ {{ number_format($vehiculo->precio_expocar ?? 0, 0, ',', '.') }}</div>
            </div>

            <div class="celda accesorios">
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