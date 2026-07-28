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
            width: 640px;
            margin: 0 auto;
            background: #fff;
        }
        .encabezado {
            background: #111;
            border-radius: 6px;
            padding: 8px 0;
            margin: 10px;
            height: 84px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .encabezado img {
            height: 70px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 16px;
            padding: 18px;
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
            font-size: 11px;
            text-transform: uppercase;
            color: #111;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .valor {
            flex: 1;
            background: #efefef;
            border-radius: 4px;
            padding: 6px 10px;
            min-height: 42px;
            font-weight: bold;
            word-break: break-word;
            display: flex;
            align-items: center;
        }
        .placa .valor {
            font-size: 64px;
            font-weight: 900;
            letter-spacing: 2px;
        }
        .marca .valor {
            font-size: 46px;
            font-weight: 900;
        }
        .linea .valor {
            font-size: 44px;
            font-weight: 900;
        }
        .modelo .valor {
            font-size: 48px;
            font-weight: 900;
        }
        .kilometraje .valor {
            font-size: 50px;
            font-weight: 900;
        }
        .prnormal .valor {
            background: #fff;
            font-size: 30px;
            font-weight: 900;
        }
        .bono .valor {
            background: #fff;
            font-size: 32px;
            font-weight: 900;
        }
        .valor.grande {
            background: #fff;
            color: #666;
            border: 3px solid #333;
            border-radius: 10px;
            font-size: 78px;
            font-weight: 900;
            justify-content: center;
            padding: 14px;
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
        .accesorios .valor {
            align-items: flex-start;
            font-size: 20px;
            line-height: 1.45;
            font-weight: 700;
            padding: 15px;
        }
        .acciones {
            width: 640px;
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

    <script>
        (function () {
            var hoja = document.querySelector('.hoja');
            // Alto útil aproximado de una hoja Carta con el margen de @page (8mm).
            // Solo actúa como red de seguridad si el contenido se pasa de una
            // hoja (ej. Accesorios con texto muy largo); en el caso normal no
            // debería reducir el zoom.
            var ALTO_UTIL_PX = 980;

            function ajustarParaImprimir() {
                hoja.style.zoom = 1;
                var alturaNatural = hoja.scrollHeight;
                var factor = Math.min(1, ALTO_UTIL_PX / alturaNatural);
                hoja.style.zoom = factor;
            }

            function restaurar() {
                hoja.style.zoom = 1;
            }

            window.addEventListener('beforeprint', ajustarParaImprimir);
            window.addEventListener('afterprint', restaurar);
        })();
    </script>

</body>
</html>
