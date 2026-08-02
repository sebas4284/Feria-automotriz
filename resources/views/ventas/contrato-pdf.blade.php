<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de compraventa — Venta #{{ $venta->id }}</title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #111;
            background: #e5e5e5;
            margin: 0;
            padding: 24px;
        }

        .hoja {
            max-width: 850px;
            margin: 0 auto;
            background: #fff;
            padding: 32px;
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            margin: 0 0 4px;
        }

        .subtitulo {
            text-align: center;
            font-size: 12px;
            color: #444;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 13px;
            text-transform: uppercase;
            margin: 18px 0 6px;
        }

        .datos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 24px;
            margin-bottom: 4px;
        }

        .dato .etiqueta {
            font-weight: bold;
        }

        p {
            margin: 6px 0;
            text-align: justify;
        }

        .firmas {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            margin-top: 60px;
            text-align: center;
        }

        .firmas .linea {
            border-top: 1px solid #111;
            margin-bottom: 4px;
            padding-top: 6px;
        }

        .acciones {
            max-width: 850px;
            margin: 16px auto 0;
            text-align: right;
        }

        .acciones a, .acciones button {
            display: inline-block;
            background: #374151;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            margin-left: 8px;
            cursor: pointer;
        }

        .acciones button {
            background: #2563eb;
        }

        @media print {
            @page {
                size: letter portrait;
                margin: 12mm;
            }

            body {
                background: #fff !important;
                padding: 0;
            }

            .hoja {
                max-width: 100%;
                padding: 0;
                border-radius: 0;
            }

            .acciones {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    @php
        $vendedor = $venta->vehiculo->concesionario;
        $comprador = $venta->comprador;
        $vehiculo = $venta->vehiculo;
        $blanco = '_______________';
        $formaPagoLabel = [
            'Contado' => 'Contado',
            'Credito' => 'Crédito',
            'Credito y Contado' => 'Crédito y Contado',
        ][$venta->forma_pago] ?? $venta->forma_pago;
    @endphp

    <div class="hoja">

        <h1>Contrato de compraventa de vehículo automotor</h1>
        <p class="subtitulo">No. {{ $venta->id }} — {{ $venta->ciudad_firma ?: $blanco }}, {{ $venta->fecha_venta?->translatedFormat('d \\d\\e F \\d\\e Y') ?? $blanco }}</p>

        <h2>Datos del vendedor</h2>
        <div class="datos">
            <div class="dato"><span class="etiqueta">Razón social:</span> {{ $vendedor->nombre ?? $blanco }}</div>
            <div class="dato"><span class="etiqueta">NIT:</span> {{ $vendedor->nit ?: $blanco }}</div>
            <div class="dato"><span class="etiqueta">Dirección:</span> {{ $vendedor->direccion ?: $blanco }}</div>
            <div class="dato"><span class="etiqueta">Teléfono:</span> {{ $vendedor->telefono ?: $blanco }}</div>
        </div>

        <h2>Datos del comprador</h2>
        <div class="datos">
            <div class="dato"><span class="etiqueta">Nombre:</span> {{ $comprador->nombre ?? $blanco }}</div>
            <div class="dato"><span class="etiqueta">Documento:</span> {{ $comprador->tipo_documento ?: 'CC' }} {{ $comprador->identificacion ?? $blanco }}</div>
            <div class="dato"><span class="etiqueta">Expedida en:</span> {{ $comprador->lugar_expedicion ?: $blanco }}</div>
            <div class="dato"><span class="etiqueta">Fecha expedición:</span> {{ $comprador->fecha_expedicion?->format('d/m/Y') ?? $blanco }}</div>
            <div class="dato"><span class="etiqueta">Dirección:</span> {{ $comprador->direccion ?: $blanco }}</div>
            <div class="dato"><span class="etiqueta">Teléfono:</span> {{ $comprador->telefono ?: $blanco }}</div>
        </div>

        <p>Las partes acuerdan celebrar el presente contrato de compraventa, el cual se regirá por las estipulaciones aquí pactadas, las normas legales aplicables a la materia y, en especial, por las siguientes cláusulas:</p>

        <h2>Primera. Objeto del contrato</h2>
        <p>El vendedor transfiere a título de venta y el comprador adquiere la propiedad del vehículo automotor que se identifica a continuación:</p>
        <div class="datos">
            <div class="dato"><span class="etiqueta">Placa:</span> {{ $vehiculo->placa ?? $blanco }}</div>
            <div class="dato"><span class="etiqueta">Marca:</span> {{ $vehiculo->marca ?? $blanco }}</div>
            <div class="dato"><span class="etiqueta">Línea / Versión:</span> {{ trim(($vehiculo->linea ?? '') . ' ' . ($vehiculo->version ?? '')) ?: $blanco }}</div>
            <div class="dato"><span class="etiqueta">Modelo:</span> {{ $vehiculo->modelo ?? $blanco }}</div>
            <div class="dato"><span class="etiqueta">Color:</span> {{ $vehiculo->color ?: $blanco }}</div>
            <div class="dato"><span class="etiqueta">Cilindraje:</span> {{ $vehiculo->cc ?? $blanco }}</div>
            <div class="dato"><span class="etiqueta">Combustible:</span> {{ $vehiculo->combustible ?: $blanco }}</div>
            <div class="dato"><span class="etiqueta">Transmisión:</span> {{ $vehiculo->transmision ?: $blanco }}</div>
            <div class="dato"><span class="etiqueta">Motor:</span> {{ $blanco }}</div>
            <div class="dato"><span class="etiqueta">Chasis:</span> {{ $blanco }}</div>
        </div>

        <h2>Segunda. Precio</h2>
        <p>Como precio del vehículo las partes acuerdan la suma de <strong>$ {{ number_format($venta->valor, 0, ',', '.') }}</strong>.</p>

        <h2>Tercera. Forma de pago</h2>
        <p>
            El comprador se compromete a pagar el precio de que trata la cláusula anterior mediante la modalidad de <strong>{{ $formaPagoLabel }}</strong>{{ $venta->banco ? ", a través de la entidad {$venta->banco}" : '' }}.
            @if($venta->tiene_retoma)
                Como parte del pago se recibe un vehículo en retoma por valor de $ {{ number_format($venta->retoma_valor, 0, ',', '.') }} ({{ $venta->retoma_descripcion }}).
            @endif
        </p>

        <h2>Cuarta. Responsabilidad por tenencia</h2>
        <p>Si las partes acuerdan la entrega del vehículo antes de realizar el traspaso de propiedad, el comprador se compromete a responder por todas las circunstancias derivadas del uso y goce del vehículo (multas, sanciones de tránsito y transporte, daños y lesiones a terceros).</p>

        <h2>Quinta. Obligaciones del vendedor</h2>
        <p>El vendedor se obliga a hacer entrega del vehículo libre de gravámenes, embargos, multas, impuestos, pactos de reserva de dominio y cualquier otra circunstancia que afecte su libre comercio.</p>
        <p><strong>Parágrafo 1.</strong> Los gastos de registro y traspaso se pagarán en las siguientes proporciones: Vendedor {{ $venta->porcentaje_gastos_vendedor !== null ? $venta->porcentaje_gastos_vendedor . '%' : $blanco }} y Comprador {{ $venta->porcentaje_gastos_comprador !== null ? $venta->porcentaje_gastos_comprador . '%' : $blanco }}; los demás gastos ocasionados se liquidarán de acuerdo con la ley.</p>
        <p><strong>Parágrafo 2.</strong> El vendedor y el comprador se obligan a realizar el traspaso dentro de los ({{ $venta->dias_traspaso ?? $blanco }}) días siguientes a la firma del presente contrato, una vez el vehículo esté cancelado en su totalidad.</p>

        <h2>Sexta. Reserva de propiedad</h2>
        <p>El vendedor se reserva la propiedad del vehículo hasta la cancelación total del mismo, de conformidad con el artículo 952 del Código de Comercio.</p>

        <h2>Séptima. Uso y tenencia — sin garantía</h2>
        <p>El comprador se hace responsable por el uso y tenencia del vehículo, exonerando al vendedor de multas y comparendos. El comprador declara haber recibido el vehículo a entera satisfacción y renuncia a cualquier reclamación de índole mecánica, al tratarse de un vehículo usado, por lo cual no cuenta con garantía.</p>

        <h2>Octava. Arras y cláusula penal</h2>
        <p>Las partes establecen como sanción pecuniaria a cargo de quien incumpla cualquiera de las estipulaciones de este contrato la suma de {{ $venta->clausula_penal_smmlv ?? $blanco }} SMMLV, exigibles desde el día siguiente al incumplimiento, tomándose el presente contrato como título ejecutivo. El valor del SMMLV a aplicar será el vigente a la fecha del incumplimiento.</p>

        <h2>Novena. Cláusula compromisoria</h2>
        <p>Toda controversia derivada de este contrato se resolverá por un mecanismo alternativo de justicia, como un Centro de Arbitraje o Conciliación; de no llegarse a un arreglo, se podrá acudir a la Justicia Ordinaria.</p>

        <p>Este contrato se firma en dos (2) ejemplares iguales, ante un (1) testigo, en la ciudad de {{ $venta->ciudad_firma ?: $blanco }}, el {{ $venta->fecha_venta?->translatedFormat('d \\d\\e F \\d\\e Y') ?? $blanco }}.</p>

        <div class="firmas">
            <div>
                <div class="linea">{{ $vendedor->nombre ?? $blanco }}</div>
                EL VENDEDOR<br>NIT/CC: {{ $vendedor->nit ?: $blanco }}
            </div>
            <div>
                <div class="linea">{{ $comprador->nombre ?? $blanco }}</div>
                EL COMPRADOR<br>CC: {{ $comprador->identificacion ?? $blanco }}
            </div>
            <div>
                <div class="linea">{{ $venta->testigo_nombre ?: $blanco }}</div>
                TESTIGO<br>CC: {{ $venta->testigo_identificacion ?: $blanco }}
            </div>
        </div>

    </div>

    <div class="acciones">
        <a href="{{ route('ventas.contrato', $venta) }}">Volver</a>
        <button onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

</body>
</html>
