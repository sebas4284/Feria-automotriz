<!-- INFORMACIÓN GENERAL -->

<h2 class="text-xl font-bold text-blue-400 mb-6">
    Información General
</h2>

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
        Placa (6 caracteres, ej: ABC123)
    </label>

    <input
        type="text"
        name="placa"
        maxlength="6"
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

@php
    $marcaActual = old('marca', $vehiculo->marca ?? '');
@endphp

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Marca
    </label>

    <select
        name="marca"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
        <option value="">Seleccionar</option>
        @foreach($marcas as $m)
            <option value="{{ $m }}" @selected($marcaActual == $m)>{{ $m }}</option>
        @endforeach
        @if($marcaActual && ! $marcas->contains($marcaActual))
            <option value="{{ $marcaActual }}" selected>{{ $marcaActual }}</option>
        @endif
    </select>

    <p class="text-xs text-gray-500 mt-1">
        ¿Falta una marca? Agrégala en <a href="{{ route('catalogos.index', 'marca') }}" class="text-blue-400 hover:underline">Catálogos</a>.
    </p>
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Línea
    </label>

    <input
        type="text"
        name="linea"
        value="{{ old('linea', $vehiculo->linea ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Versión
    </label>

    <input
        type="text"
        name="version"
        value="{{ old('version', $vehiculo->version ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
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
    $colorActual = old('color', $vehiculo->color ?? '');
    $combustibleActual = old('combustible', $vehiculo->combustible ?? '');
@endphp

<div>
    <label class="block mb-2 text-sm text-gray-400">Color</label>
    <select
        name="color"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
        <option value="">Seleccionar</option>
        @foreach($colores as $c)
            <option value="{{ $c }}" @selected($colorActual == $c)>{{ $c }}</option>
        @endforeach
        @if($colorActual && ! $colores->contains($colorActual))
            <option value="{{ $colorActual }}" selected>{{ $colorActual }}</option>
        @endif
    </select>
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Clase de Vehículo</label>
    <input
        type="text"
        name="clase_vehiculo"
        value="{{ old('clase_vehiculo', $vehiculo->clase_vehiculo ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Tipo de Vehículo</label>
    <input
        type="text"
        name="tipo_vehiculo"
        value="{{ old('tipo_vehiculo', $vehiculo->tipo_vehiculo ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Cilindraje (CC)</label>
    <input
        type="number"
        name="cc"
        value="{{ old('cc', $vehiculo->cc ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Combustible</label>
    <select
        name="combustible"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
        <option value="">Seleccionar</option>
        @foreach($combustibles as $c)
            <option value="{{ $c }}" @selected($combustibleActual == $c)>{{ $c }}</option>
        @endforeach
        @if($combustibleActual && ! $combustibles->contains($combustibleActual))
            <option value="{{ $combustibleActual }}" selected>{{ $combustibleActual }}</option>
        @endif
    </select>
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Transmisión</label>
    <select
        name="transmision"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

        <option value="">Seleccionar</option>

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
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Kilometraje</label>
    <input
        type="number"
        name="kilometraje"
        value="{{ old('kilometraje', $vehiculo->kilometraje ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
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
        value="{{ old('fecha_matricula', isset($vehiculo) && $vehiculo->fecha_matricula ? \Carbon\Carbon::parse($vehiculo->fecha_matricula)->format('Y-m-d') : '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>
<div>
    <label class="block mb-2 text-sm text-gray-400">
        Ciudad Matrícula
    </label>
    @php
        $ciudadActual = old('ciudad_matricula', $vehiculo->ciudad_matricula ?? '');
    @endphp
    <select
        name="ciudad_matricula"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
        <option value="">Seleccionar</option>
        @foreach($ciudades as $c)
            <option value="{{ $c }}" @selected($ciudadActual == $c)>{{ $c }}</option>
        @endforeach
        @if($ciudadActual && ! $ciudades->contains($ciudadActual))
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
        value="{{ old('fecha_soat', isset($vehiculo) && $vehiculo->fecha_soat ? \Carbon\Carbon::parse($vehiculo->fecha_soat)->format('Y-m-d') : '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">
        Fecha Tecnomecánica
    </label>

    <input
        type="date"
        name="fecha_tecno"
        value="{{ old('fecha_tecno', isset($vehiculo) && $vehiculo->fecha_tecno ? \Carbon\Carbon::parse($vehiculo->fecha_tecno)->format('Y-m-d') : '') }}"
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

<div class="grid grid-cols-1 md:grid-cols-3 gap-6"
    x-data="{
        prFasecolda: {{ (float) old('pr_fasecolda', $vehiculo->pr_fasecolda ?? 0) }},
        normal: {{ (float) old('precio_normal', $vehiculo->precio_normal ?? 0) }},
        bono: {{ (float) old('bono_descuento', $vehiculo->bono_descuento ?? 0) }},
        get expocar() { return this.normal - this.bono },
        cop(n) { return n ? new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(n) : ''; },
        parseCop(str) { return parseInt(String(str).replace(/[^\d]/g, ''), 10) || 0; },
    }">


<div>
    <label class="block mb-2 text-sm text-gray-400">Código Fasecolda</label>
    <input
        type="text"
        name="cod_fasecolda"
        value="{{ old('cod_fasecolda', $vehiculo->cod_fasecolda ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Precio Fasecolda</label>
    <input
        type="text"
        inputmode="numeric"
        :value="cop(prFasecolda)"
        @input="prFasecolda = parseCop($event.target.value)"
        placeholder="$ 0"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
    <input type="hidden" name="pr_fasecolda" :value="prFasecolda">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Precio Normal</label>
    <input
        type="text"
        inputmode="numeric"
        :value="cop(normal)"
        @input="normal = parseCop($event.target.value)"
        placeholder="$ 0"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
    <input type="hidden" name="precio_normal" :value="normal">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Bono Descuento</label>
    <input
        type="text"
        inputmode="numeric"
        :value="cop(bono)"
        @input="bono = parseCop($event.target.value)"
        placeholder="$ 0"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
    <input type="hidden" name="bono_descuento" :value="bono">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Precio Expocar (automático)</label>
    <input
        type="text"
        :value="cop(expocar)"
        readonly
        class="w-full bg-gray-900 text-gray-400 cursor-not-allowed border border-gray-700 rounded-xl px-4 py-3">
    <input type="hidden" name="precio_expocar" :value="expocar">
</div>

<div>
    <label class="block mb-2 text-sm text-gray-400">Estado</label>
    <select
        name="estado"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

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

<div>
    <label class="block mb-2 text-sm text-gray-400">Ubicación</label>
    <select
        name="ubicacion"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

        <option value="Dentro del área"
            @selected(old('ubicacion', $vehiculo->ubicacion ?? 'Dentro del área') == 'Dentro del área')>
            Dentro del área (ocupa cupo de feria)
        </option>

        <option value="Fuera del área"
            @selected(old('ubicacion', $vehiculo->ubicacion ?? 'Dentro del área') == 'Fuera del área')>
            Fuera del área (no ocupa cupo)
        </option>

    </select>
</div>


</div>
