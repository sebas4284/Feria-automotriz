@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto" x-data="contratoForm()">

    <div class="mb-6">
        <a href="{{ route('ventas.show', $venta) }}" class="text-sm text-blue-400 hover:underline">&larr; Volver a la venta</a>
        <h1 class="text-2xl lg:text-3xl font-bold mt-2">Generar contrato — Venta #{{ $venta->id }}</h1>
        <p class="text-gray-400 mt-1 text-sm">Completa los datos que falten y luego genera el contrato para imprimir.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-xl p-4">
            <p class="text-red-400 font-semibold mb-2">Errores:</p>
            <ul class="text-red-400 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===================== ESCANEO DE CÉDULA ===================== --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-semibold mb-1">Escanear cédula (reverso)</h2>
        <p class="text-xs text-gray-500 mb-4">
            Lee el código de barras del reverso de la cédula digital para confirmar que coincide con el comprador registrado. No reemplaza los datos de la venta, solo ayuda a verificar y a completar el tipo de documento.
        </p>

        <button type="button" @click="escaneando ? detenerEscaneo() : iniciarEscaneo()"
            class="bg-blue-600 hover:bg-blue-700 px-5 py-2 rounded-xl text-sm font-medium transition">
            <span x-show="!escaneando">Escanear cédula</span>
            <span x-show="escaneando" x-cloak>Detener escaneo</span>
        </button>

        <p x-show="errorEscaneo" x-cloak class="text-sm text-red-400 mt-3" x-text="errorEscaneo"></p>

        <div x-show="escaneando" x-cloak class="mt-4">
            <video x-ref="video" class="w-full max-w-sm rounded-xl border border-gray-700" muted playsinline></video>
        </div>

        <div x-show="lecturaTexto" x-cloak class="mt-4 bg-gray-800 border border-gray-700 rounded-xl p-4">
            <p class="text-sm text-gray-300">
                Cédula leída: <span class="font-semibold" x-text="(lectura.nombres + ' ' + lectura.apellidos).trim() || '—'"></span>
                — <span class="font-mono" x-text="lectura.identificacion || '—'"></span>
            </p>
            <p class="text-xs mt-1" :class="coincideConComprador ? 'text-emerald-400' : 'text-amber-400'">
                <span x-show="coincideConComprador">Coincide con el comprador registrado ({{ $venta->comprador->nombre }}).</span>
                <span x-show="!coincideConComprador">No coincide con el comprador registrado ({{ $venta->comprador->nombre }} — {{ $venta->comprador->identificacion }}). Verifica antes de continuar.</span>
            </p>
            <details class="mt-2">
                <summary class="text-xs text-gray-500 cursor-pointer">Ver texto crudo leído</summary>
                <p class="text-xs text-gray-500 mt-1 break-all" x-text="lecturaTexto"></p>
            </details>
        </div>
    </div>

    <form method="POST" action="{{ route('ventas.contrato.update', $venta) }}" class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        @csrf
        @method('PATCH')

        <h2 class="text-xl font-semibold mb-4">Datos del comprador</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block mb-2 text-sm text-gray-400">Tipo de documento</label>
                <select name="comprador_tipo_documento" x-model="tipoDocumento" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
                    <option value="CC">Cédula de ciudadanía (CC)</option>
                    <option value="CE">Cédula de extranjería (CE)</option>
                    <option value="NIT">NIT</option>
                    <option value="Pasaporte">Pasaporte</option>
                </select>
            </div>
            <div>
                <label class="block mb-2 text-sm text-gray-400">Lugar de expedición</label>
                <input type="text" name="comprador_lugar_expedicion"
                    value="{{ old('comprador_lugar_expedicion', $venta->comprador->lugar_expedicion) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
            <div>
                <label class="block mb-2 text-sm text-gray-400">Fecha de expedición</label>
                <input type="date" name="comprador_fecha_expedicion"
                    value="{{ old('comprador_fecha_expedicion', $venta->comprador->fecha_expedicion?->format('Y-m-d')) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
        </div>

        <h2 class="text-xl font-semibold mb-4">Datos del contrato</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block mb-2 text-sm text-gray-400">Ciudad de firma</label>
                <input type="text" name="ciudad_firma" value="{{ old('ciudad_firma', $venta->ciudad_firma) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
            <div>
                <label class="block mb-2 text-sm text-gray-400">Días para el traspaso</label>
                <input type="number" min="0" max="365" name="dias_traspaso" value="{{ old('dias_traspaso', $venta->dias_traspaso) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
            <div>
                <label class="block mb-2 text-sm text-gray-400">% gastos de traspaso — Vendedor</label>
                <input type="number" min="0" max="100" name="porcentaje_gastos_vendedor" value="{{ old('porcentaje_gastos_vendedor', $venta->porcentaje_gastos_vendedor) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
            <div>
                <label class="block mb-2 text-sm text-gray-400">% gastos de traspaso — Comprador</label>
                <input type="number" min="0" max="100" name="porcentaje_gastos_comprador" value="{{ old('porcentaje_gastos_comprador', $venta->porcentaje_gastos_comprador) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
            <div>
                <label class="block mb-2 text-sm text-gray-400">Cláusula de arras/penal (SMMLV)</label>
                <input type="number" min="0" step="0.01" name="clausula_penal_smmlv" value="{{ old('clausula_penal_smmlv', $venta->clausula_penal_smmlv) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
        </div>

        <h2 class="text-xl font-semibold mb-4">Testigo (opcional)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block mb-2 text-sm text-gray-400">Nombre del testigo</label>
                <input type="text" name="testigo_nombre" value="{{ old('testigo_nombre', $venta->testigo_nombre) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
            <div>
                <label class="block mb-2 text-sm text-gray-400">Identificación del testigo</label>
                <input type="text" name="testigo_identificacion" value="{{ old('testigo_identificacion', $venta->testigo_identificacion) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium transition">
                Guardar datos del contrato
            </button>
            <a href="{{ route('ventas.contrato.pdf', $venta) }}" target="_blank"
                class="bg-gray-800 hover:bg-gray-700 px-6 py-3 rounded-xl font-medium transition text-center">
                Ver contrato para imprimir
            </a>
        </div>
    </form>

</div>

<script>
function contratoForm() {
    return {
        tipoDocumento: {{ Js::from(old('comprador_tipo_documento', $venta->comprador->tipo_documento ?? 'CC')) }},
        compradorIdentificacion: {{ Js::from($venta->comprador->identificacion ?? '') }},
        escaneando: false,
        errorEscaneo: '',
        lecturaTexto: '',
        lectura: { nombres: '', apellidos: '', identificacion: '' },
        controlesEscaneo: null,

        get coincideConComprador() {
            return this.lectura.identificacion && this.lectura.identificacion === this.compradorIdentificacion;
        },

        async iniciarEscaneo() {
            this.errorEscaneo = '';
            this.escaneando = true;

            try {
                const modulo = await window.cargarCedulaScanner();
                await this.$nextTick();
                this.controlesEscaneo = await modulo.iniciarEscaneoPdf417(
                    this.$refs.video,
                    (texto) => {
                        this.lecturaTexto = texto;
                        this.lectura = modulo.parsearCedulaPdf417(texto);
                        if (!this.tipoDocumento) {
                            this.tipoDocumento = 'CC';
                        }
                    },
                    (error) => {
                        this.errorEscaneo = 'No se pudo leer el código: ' + (error?.message ?? error);
                    }
                );
            } catch (error) {
                this.errorEscaneo = 'No se pudo acceder a la cámara: ' + (error?.message ?? error);
                this.escaneando = false;
            }
        },

        detenerEscaneo() {
            this.controlesEscaneo?.stop();
            this.controlesEscaneo = null;
            this.escaneando = false;
        },
    };
}
</script>

@endsection
