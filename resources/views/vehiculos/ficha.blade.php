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
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #e5e5e5;
            margin: 0;
            padding: 24px;
        }
        .hoja {
            width: 480px;
            margin: 0 auto;
            background: #fff;
        }
        .encabezado {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #111;
            border-radius: 10px;
            margin: 12px;
            padding: 10px;
        }
        .encabezado img {
            max-width: 100%;
            height: 52px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px 12px;
            padding: 0 14px 14px;
            grid-template-areas:
                "placa       vitrina"
                "marca       marca"
                "linea       linea"
                "fecha       ciudad"
                "modelo      cilindraje"
                "combustible transmision"
                "kilometraje kilometraje"
                "soat        tecno"
                "fasecolda   accesorios"
                "prnormal    accesorios"
                "bono        accesorios"
                "prferia     prferia";
        }
        .celda {
            display: flex;
            flex-direction: column;
        }
        .etiqueta {
            font-size: 10px;
            text-transform: uppercase;
            color: #16215c;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .valor {
            flex: 1;
            background: #e4e4e4;
            border-radius: 7px;
            padding: 6px 9px;
            font-size: 14px;
            font-weight: bold;
            word-break: break-word;
            display: flex;
            align-items: center;
        }
        .valor.destacado {
            font-size: 20px;
            padding: 9px 11px;
        }
        .valor.grande {
            font-size: 34px;
            font-weight: 900;
            color: #c8161e;
            background: #fdeaea;
            border: 2px solid #c8161e;
            padding: 12px 14px;
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
        .fasecolda { grid-area: fasecolda; }
        .prnormal { grid-area: prnormal; }
        .bono { grid-area: bono; }
        .prferia { grid-area: prferia; }
        .accesorios { grid-area: accesorios; }
        .accesorios .valor { align-items: flex-start; font-size: 12px; }
        .acciones {
            width: 480px;
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
            @page {
                size: letter;
                margin: 8mm;
            }
            body { background: #fff; padding: 0; }
            .hoja {
                width: 100%;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .grid, .encabezado, .celda {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .acciones { display: none; }
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
                <div class="valor destacado">{{ $vehiculo->marca ?: '—' }}</div>
            </div>

            <div class="celda linea">
                <div class="etiqueta">Línea - Versión</div>
                <div class="valor destacado">{{ trim(($vehiculo->linea ?? '') . ' ' . ($vehiculo->version ?? '')) ?: '—' }}</div>
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
                <div class="valor destacado">{{ $vehiculo->kilometraje !== null ? number_format($vehiculo->kilometraje, 0, ',', '.') . ' km' : '—' }}</div>
            </div>

            <div class="celda soat">
                <div class="etiqueta">SOAT</div>
                <div class="valor">{{ $vehiculo->fecha_soat ? \Carbon\Carbon::parse($vehiculo->fecha_soat)->format('d/m/Y') : '—' }}</div>
            </div>

            <div class="celda tecno">
                <div class="etiqueta">Tecno</div>
                <div class="valor">{{ $vehiculo->fecha_tecno ? \Carbon\Carbon::parse($vehiculo->fecha_tecno)->format('d/m/Y') : '—' }}</div>
            </div>

            <div class="celda fasecolda">
                <div class="etiqueta">Cod Fasecolda</div>
                <div class="valor">{{ $vehiculo->cod_fasecolda ?: '—' }}</div>
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
