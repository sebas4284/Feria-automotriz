{{--
    Parámetros:
    $venta          — instancia de Venta al editar, null al crear
    $clientes, $vehiculos, $concesionarios, $compradores, $vehiculosJson, $asesoresJson
--}}

<div x-data="ventaForm()">

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

    @if (session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-xl p-4 text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <form
        action="{{ $venta ? route('ventas.update', $venta) : route('ventas.store') }}"
        method="POST"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

        @csrf
        @if($venta)
            @method('PUT')
        @endif

        {{-- ===================== COMPRADOR ===================== --}}
        <h2 class="text-xl font-semibold mb-4">Datos del comprador</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

            <div>
                <label class="block mb-2 text-sm text-gray-400">Identificación (cédula)</label>
                <input type="text" name="comprador_identificacion" x-model="comprador.identificacion"
                    @blur="buscarComprador()" value="{{ old('comprador_identificacion', $venta->comprador->identificacion ?? '') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Nombre y apellidos</label>
                <input type="text" name="comprador_nombre" x-model="comprador.nombre"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Teléfono</label>
                <input type="text" name="comprador_telefono" x-model="comprador.telefono"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Dirección</label>
                <input type="text" name="comprador_direccion" x-model="comprador.direccion"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Correo</label>
                <input type="email" name="comprador_correo" x-model="comprador.correo"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Cliente de feria relacionado (opcional)</label>
                <select name="cliente_id" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
                    <option value="">Sin relacionar</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" @selected((int) old('cliente_id', $venta->cliente_id ?? null) === $cliente->id)>{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- ===================== VEHÍCULO Y VENTA ===================== --}}
        <h2 class="text-xl font-semibold mb-4">Datos de la venta</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block mb-2 text-sm text-gray-400">Vehículo (placa)</label>
                <div class="relative" @click.outside="mostrarSugerenciasVehiculo = false">
                    <input type="hidden" name="vehiculo_id" :value="vehiculoId">
                    <input type="text" x-model="vehiculoQuery" autocomplete="off"
                        @input="vehiculoId = ''; mostrarSugerenciasVehiculo = true"
                        @focus="mostrarSugerenciasVehiculo = true"
                        placeholder="Buscar por placa, marca o línea..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
                    <div x-show="mostrarSugerenciasVehiculo && vehiculoQuery.trim() !== ''" x-cloak
                        class="absolute z-10 mt-1 w-full bg-gray-800 border border-gray-700 rounded-xl max-h-60 overflow-y-auto shadow-lg">
                        <template x-for="v in vehiculosFiltrados" :key="v.id">
                            <button type="button" @click="seleccionarVehiculo(v)"
                                class="w-full text-left px-4 py-2.5 hover:bg-gray-700 text-sm border-b border-gray-700 last:border-b-0">
                                <span x-text="v.placa" class="font-mono font-bold"></span>
                                <span x-text="' — ' + v.marca + ' ' + v.modelo"></span>
                            </button>
                        </template>
                        <div x-show="vehiculosFiltrados.length === 0" class="px-4 py-3 text-sm text-gray-500">
                            Sin resultados
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Concesionario que lo tiene</label>
                <div class="w-full bg-gray-800/50 border border-gray-800 rounded-xl px-4 py-3 text-gray-400">
                    <span x-text="concesionarioTiene || '—'"></span>
                </div>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Valor de venta</label>
                <input type="number" name="valor" x-model="valor" step="0.01"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Concesionario que lo va a vender</label>
                <select name="concesionario_vende_id" x-model="concesionarioVende"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
                    <option value="">Seleccione...</option>
                    @foreach($concesionarios as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Asesor comercial</label>
                <select name="asesor_comercial_id" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
                    <option value="">Seleccione...</option>
                    <template x-for="a in asesoresFiltrados" :key="a.id">
                        <option :value="a.id" x-text="a.nombre + ' — CC ' + a.cedula" :selected="a.id == asesorSeleccionado"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Fecha</label>
                <input type="date" name="fecha_venta" value="{{ old('fecha_venta', optional($venta->fecha_venta ?? null)->format('Y-m-d') ?? date('Y-m-d')) }}"
                    max="{{ now()->format('Y-m-d') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-400">Forma de pago</label>
                <select name="forma_pago" x-model="formaPago" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3" required>
                    <option value="">Seleccione...</option>
                    <option value="Contado">Contado</option>
                    <option value="Credito">Crédito</option>
                    <option value="Credito y Contado">Crédito y Contado</option>
                </select>
            </div>

            <div x-show="formaPago === 'Credito' || formaPago === 'Credito y Contado'">
                <label class="block mb-2 text-sm text-gray-400">Banco</label>
                @php $bancoActual = old('banco', $venta->banco ?? ''); @endphp
                <select name="banco" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
                    <option value="">Seleccione...</option>
                    @foreach($bancos as $b)
                        <option value="{{ $b }}" @selected($bancoActual == $b)>{{ $b }}</option>
                    @endforeach
                    @if($bancoActual && ! $bancos->contains($bancoActual))
                        <option value="{{ $bancoActual }}" selected>{{ $bancoActual }}</option>
                    @endif
                </select>
            </div>

            <div class="flex items-center gap-3 md:pt-8">
                <input type="checkbox" name="participa_experiencia" id="participa_experiencia" value="1"
                    x-model="participaExperiencia"
                    class="w-5 h-5 rounded bg-gray-800 border-gray-700 text-blue-600 focus:ring-blue-500">
                <label for="participa_experiencia" class="text-sm text-gray-400">
                    ¿Participa en la experiencia?
                </label>
            </div>

            <div x-show="participaExperiencia">
                <label class="block mb-2 text-sm text-gray-400">Detalle de la experiencia</label>
                @php $detalleActual = old('detalle_experiencia', $venta->detalle_experiencia ?? ''); @endphp
                <select name="detalle_experiencia" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
                    <option value="">Seleccione...</option>
                    @foreach($detallesExperiencia as $d)
                        <option value="{{ $d }}" @selected($detalleActual == $d)>{{ $d }}</option>
                    @endforeach
                    @if($detalleActual && ! $detallesExperiencia->contains($detalleActual))
                        <option value="{{ $detalleActual }}" selected>{{ $detalleActual }}</option>
                    @endif
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    ¿Falta un detalle? Agrégalo en <a href="{{ route('catalogos.index', 'detalle_experiencia') }}" class="text-blue-400 hover:underline">Catálogos</a>.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="tiene_retoma" id="tiene_retoma" value="1" x-model="tieneRetoma"
                    class="w-5 h-5 rounded bg-gray-800 border-gray-700 text-blue-600 focus:ring-blue-500">
                <label for="tiene_retoma" class="text-sm text-gray-400">
                    ¿Incluye retoma?
                </label>
            </div>

            <div x-show="tieneRetoma">
                <label class="block mb-2 text-sm text-gray-400">Valor de la retoma</label>
                <input type="number" name="retoma_valor" step="0.01" value="{{ old('retoma_valor', $venta->retoma_valor ?? '') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>

            <div x-show="tieneRetoma">
                <label class="block mb-2 text-sm text-gray-400">Vehículo entregado en retoma</label>
                <input type="text" name="retoma_descripcion" value="{{ old('retoma_descripcion', $venta->retoma_descripcion ?? '') }}"
                    placeholder="Ej. Mazda 3 2018, placa ABC123"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
            </div>

        </div>

        <div class="mt-6">
            <label class="block mb-2 text-sm text-gray-400">Observaciones</label>
            <textarea name="observaciones" rows="4"
                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">{{ old('observaciones', $venta->observaciones ?? '') }}</textarea>
        </div>

        <div class="mt-8 flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium">
                {{ $venta ? 'Guardar Cambios' : 'Registrar Venta' }}
            </button>
            @if($venta)
                <a href="{{ route('ventas.show', $venta) }}" class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-xl transition">
                    Cancelar
                </a>
            @endif
        </div>

    </form>

</div>

<script>
function ventaForm() {
    return {
        compradores: @json($compradores),
        vehiculos: @json($vehiculosJson),
        asesores: @json($asesoresJson),

        comprador: {
            identificacion: {{ Js::from(old('comprador_identificacion', $venta->comprador->identificacion ?? '')) }},
            nombre: {{ Js::from(old('comprador_nombre', $venta->comprador->nombre ?? '')) }},
            telefono: {{ Js::from(old('comprador_telefono', $venta->comprador->telefono ?? '')) }},
            direccion: {{ Js::from(old('comprador_direccion', $venta->comprador->direccion ?? '')) }},
            correo: {{ Js::from(old('comprador_correo', $venta->comprador->correo ?? '')) }},
        },
        vehiculoId: {{ Js::from(old('vehiculo_id', $venta->vehiculo_id ?? '')) }},
        vehiculoQuery: '',
        mostrarSugerenciasVehiculo: false,
        valor: {{ Js::from(old('valor', $venta->valor ?? '')) }},
        concesionarioTiene: {{ Js::from($venta->vehiculo->concesionario->nombre ?? '') }},
        concesionarioVende: {{ Js::from(old('concesionario_vende_id', $venta->concesionario_vende_id ?? '')) }},
        asesorSeleccionado: {{ Js::from(old('asesor_comercial_id', $venta->asesor_comercial_id ?? '')) }},
        formaPago: {{ Js::from(old('forma_pago', $venta->forma_pago ?? '')) }},
        tieneRetoma: {{ Js::from((bool) old('tiene_retoma', $venta->tiene_retoma ?? false)) }},
        participaExperiencia: {{ Js::from((bool) old('participa_experiencia', $venta->participa_experiencia ?? true)) }},

        init() {
            const v = this.vehiculos.find(v => v.id == this.vehiculoId);
            if (v) {
                this.vehiculoQuery = `${v.placa} — ${v.marca} ${v.modelo}`;
            }
        },

        get vehiculosFiltrados() {
            const q = this.vehiculoQuery.trim().toLowerCase();
            if (!q) return [];
            return this.vehiculos.filter(v =>
                v.placa.toLowerCase().includes(q) ||
                v.marca.toLowerCase().includes(q) ||
                String(v.modelo).toLowerCase().includes(q)
            ).slice(0, 20);
        },

        seleccionarVehiculo(v) {
            this.vehiculoId = v.id;
            this.vehiculoQuery = `${v.placa} — ${v.marca} ${v.modelo}`;
            this.mostrarSugerenciasVehiculo = false;
            this.onVehiculoChange();
        },

        buscarComprador() {
            const match = this.compradores.find(c => c.identificacion === this.comprador.identificacion);
            if (match) {
                this.comprador.nombre = match.nombre;
                this.comprador.telefono = match.telefono ?? '';
                this.comprador.direccion = match.direccion ?? '';
                this.comprador.correo = match.correo ?? '';
            }
        },

        onVehiculoChange() {
            const v = this.vehiculos.find(v => v.id == this.vehiculoId);
            if (!v) return;
            this.valor = v.precio_expocar ?? '';
            this.concesionarioTiene = v.concesionario_nombre;
            this.concesionarioVende = v.concesionario_id ?? '';
        },

        get asesoresFiltrados() {
            return this.concesionarioVende
                ? this.asesores.filter(a => a.concesionario_id == this.concesionarioVende)
                : this.asesores;
        },
    };
}
</script>
