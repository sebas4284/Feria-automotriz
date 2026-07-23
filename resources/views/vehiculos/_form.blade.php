<!-- INFORMACIÓN GENERAL -->

<h2 class="text-xl font-bold text-blue-400 mb-6">
    Información General
</h2>

<div
    x-data="{
        catalogo: @json($catalogoVehiculos),
        marca: @js(old('marca', $vehiculo->marca ?? '')),
        linea: @js(old('linea', $vehiculo->linea ?? '')),
        version: @js(old('version', $vehiculo->version ?? '')),
        get marcas() {
            return [...new Set(this.catalogo.map(f => f.marca))].sort();
        },
        get lineas() {
            return [...new Set(this.catalogo.filter(f => f.marca === this.marca).map(f => f.linea))].sort();
        },
        get versiones() {
            return [...new Set(this.catalogo.filter(f => f.marca === this.marca && f.linea === this.linea).map(f => f.version))].sort();
        },
        aplicarFicha() {
            const ficha = this.catalogo.find(f => f.marca === this.marca && f.linea === this.linea && f.version === this.version);
            if (ficha) {
                this.$refs.clase_vehiculo.value = ficha.clase_vehiculo;
                this.$refs.cc.value = ficha.cc;
                this.$refs.combustible.value = ficha.combustible;
            }
        },
    }"
>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">


<div>
    <label class="block mb-2 text-sm text-gray-400">
        Concesionario
    </label>

    <select
        name="concesionario_id"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

        <option value="">
            Seleccionar
        </option>

        @foreach($concesionarios as $concesionario)

            <option
                value="{{ $concesionario->id }}"
                @selected(old('concesionario_id', $vehiculo->concesionario_id ?? '') == $concesionario->id)>

                {{ $concesionario->nombre }}

            </option>

        @endforeach

    </select>
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Placa
    </label>

    <input
        type="text"
        name="placa"
        value="{{ old('placa', $vehiculo->placa ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Número de llave
    </label>

    <input
        type="text"
        name="numero_llave"
        value="{{ old('numero_llave', $vehiculo->numero_llave ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Marca
    </label>

    <select
        name="marca"
        x-model="marca"
        @change="linea = ''; version = ''"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
        <option value="">Seleccionar</option>
        <template x-for="m in marcas" :key="m">
            <option :value="m" x-text="m" :selected="m === marca"></option>
        </template>
    </select>
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Línea
    </label>

    <select
        name="linea"
        x-model="linea"
        :disabled="!marca"
        @change="version = ''"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 disabled:opacity-50">
        <option value="">Seleccionar</option>
        <template x-for="l in lineas" :key="l">
            <option :value="l" x-text="l" :selected="l === linea"></option>
        </template>
    </select>
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Versión
    </label>

    <select
        name="version"
        x-model="version"
        :disabled="!linea"
        @change="aplicarFicha()"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 disabled:opacity-50">
        <option value="">Seleccionar</option>
        <template x-for="v in versiones" :key="v">
            <option :value="v" x-text="v" :selected="v === version"></option>
        </template>
    </select>
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Modelo
    </label>

    <select
        name="modelo"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
        <option value="">Seleccionar</option>
        @for ($anio = now()->year + 1; $anio >= 1995; $anio--)
            <option value="{{ $anio }}" @selected(old('modelo', $vehiculo->modelo ?? '') == $anio)>{{ $anio }}</option>
        @endfor
    </select>
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Foto
    </label>

    @if(!empty($vehiculo) && $vehiculo->fotoUrl)
        <img
            src="{{ $vehiculo->fotoUrl }}"
            alt="Foto actual del vehículo"
            class="w-32 h-24 object-cover rounded-xl mb-2 border border-gray-700">
    @endif

    <input
        type="file"
        name="foto"
        accept="image/*"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white">
</div>


</div>

<hr class="my-8 border-gray-800">

<!-- CARACTERÍSTICAS -->

<h2 class="text-xl font-bold text-green-400 mb-6">
    Características
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">


@php
    $coloresDisponibles = ['Azul', 'Blanco', 'Gris', 'Negro', 'Plata', 'Rojo'];
    $colorActual = old('color', $vehiculo->color ?? '');

    $combustiblesDisponibles = ['Gasolina', 'ACPM (Diésel)', 'Híbrido'];
    $combustibleActual = old('combustible', $vehiculo->combustible ?? '');
@endphp

<select
    name="color"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
    <option value="">Color</option>
    @foreach($coloresDisponibles as $c)
        <option value="{{ $c }}" @selected($colorActual == $c)>{{ $c }}</option>
    @endforeach
    @if($colorActual && ! in_array($colorActual, $coloresDisponibles, true))
        <option value="{{ $colorActual }}" selected>{{ $colorActual }}</option>
    @endif
</select>

<input
    type="text"
    name="clase_vehiculo"
    x-ref="clase_vehiculo"
    placeholder="Clase de Vehículo"
    value="{{ old('clase_vehiculo', $vehiculo->clase_vehiculo ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<input
    type="text"
    name="tipo_vehiculo"
    placeholder="Tipo de Vehículo"
    value="{{ old('tipo_vehiculo', $vehiculo->tipo_vehiculo ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<input
    type="number"
    name="cc"
    x-ref="cc"
    placeholder="CC"
    value="{{ old('cc', $vehiculo->cc ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<select
    name="combustible"
    x-ref="combustible"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
    <option value="">Combustible</option>
    @foreach($combustiblesDisponibles as $c)
        <option value="{{ $c }}" @selected($combustibleActual == $c)>{{ $c }}</option>
    @endforeach
    @if($combustibleActual && ! in_array($combustibleActual, $combustiblesDisponibles, true))
        <option value="{{ $combustibleActual }}" selected>{{ $combustibleActual }}</option>
    @endif
</select>

<select
    name="transmision"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

    <option value="">Transmisión</option>

    <option value="Mecánica"
        @selected(old('transmision', $vehiculo->transmision ?? '') == 'Mecánica')>
        Mecánica
    </option>

    <option value="Automática"
        @selected(old('transmision', $vehiculo->transmision ?? '') == 'Automática')>
        Automática
    </option>

    <option value="CVT"
        @selected(old('transmision', $vehiculo->transmision ?? '') == 'CVT')>
        CVT
    </option>

</select>

<input
    type="number"
    name="kilometraje"
    placeholder="Kilometraje"
    value="{{ old('kilometraje', $vehiculo->kilometraje ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">


</div>

</div>

<hr class="my-8 border-gray-800">

<!-- DOCUMENTACIÓN -->

<h2 class="text-xl font-bold text-yellow-400 mb-6">
    Documentación
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">


<div>
    <label class="block mb-2 text-sm text-gray-400">
        Fecha Matrícula
    </label>

    <input
        type="date"
        name="fecha_matricula"
        value="{{ old('fecha_matricula', isset($vehiculo) ? optional($vehiculo->fecha_matricula)->format('Y-m-d') : '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>
<div>
    <label class="block mb-2 text-sm text-gray-400">
        Ciudad Matrícula
    </label>
    @php
        $ciudadesDisponibles = ['Barranquilla', 'Bogotá D.C.', 'Bucaramanga', 'Cali', 'Cartagena', 'Cúcuta', 'Medellín', 'Pereira'];
        $ciudadActual = old('ciudad_matricula', $vehiculo->ciudad_matricula ?? '');
    @endphp
    <select
        name="ciudad_matricula"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
        <option value="">Seleccionar</option>
        @foreach($ciudadesDisponibles as $c)
            <option value="{{ $c }}" @selected($ciudadActual == $c)>{{ $c }}</option>
        @endforeach
        @if($ciudadActual && ! in_array($ciudadActual, $ciudadesDisponibles, true))
            <option value="{{ $ciudadActual }}" selected>{{ $ciudadActual }}</option>
        @endif
    </select>
</div>
<div>
    <label class="block mb-2 text-sm text-gray-400">
        Fecha SOAT
    </label>

    <input
        type="date"
        name="fecha_soat"
        value="{{ old('fecha_soat', isset($vehiculo) ? optional($vehiculo->fecha_soat)->format('Y-m-d') : '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Fecha Tecnomecánica
    </label>

    <input
        type="date"
        name="fecha_tecno"
        value="{{ old('fecha_tecno', isset($vehiculo) ? optional($vehiculo->fecha_tecno)->format('Y-m-d') : '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>
<div>
    <label class="block mb-2 text-sm text-gray-400">
        Impuestos
    </label>

<input
    type="text"
    name="impuestos"
    placeholder="Impuestos"
    value="{{ old('impuestos', $vehiculo->impuestos ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

</div>

<div class="mt-6">


<textarea
    name="accesorios"
    rows="4"
    placeholder="Accesorios"
    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">{{ old('accesorios', $vehiculo->accesorios ?? '') }}</textarea>


</div>

<hr class="my-8 border-gray-800">

<!-- VALORES -->

<h2 class="text-xl font-bold text-purple-400 mb-6">
    Valores Comerciales
</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">


<input
    type="text"
    name="cod_fasecolda"
    placeholder="Código Fasecolda"
    value="{{ old('cod_fasecolda', $vehiculo->cod_fasecolda ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<input
    type="number"
    step="0.01"
    name="pr_fasecolda"
    placeholder="Precio Fasecolda"
    value="{{ old('pr_fasecolda', $vehiculo->pr_fasecolda ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<input
    type="number"
    step="0.01"
    name="precio_normal"
    placeholder="Precio Normal"
    value="{{ old('precio_normal', $vehiculo->precio_normal ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<input
    type="number"
    step="0.01"
    name="bono_descuento"
    placeholder="Bono Descuento"
    value="{{ old('bono_descuento', $vehiculo->bono_descuento ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<input
    type="number"
    step="0.01"
    name="precio_expocar"
    placeholder="Precio Expocar"
    value="{{ old('precio_expocar', $vehiculo->precio_expocar ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<select
    name="estado"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

    <option value="Disponible"
        @selected(old('estado', $vehiculo->estado ?? '') == 'Disponible')>
        Disponible
    </option>

    <option value="Reservado"
        @selected(old('estado', $vehiculo->estado ?? '') == 'Reservado')>
        Reservado
    </option>

    <option value="Vendido"
        @selected(old('estado', $vehiculo->estado ?? '') == 'Vendido')>
        Vendido
    </option>

</select>


</div>
