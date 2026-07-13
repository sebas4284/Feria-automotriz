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

    <input
        type="text"
        name="marca"
        value="{{ old('marca', $vehiculo->marca ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
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

    <input
        type="text"
        name="modelo"
        value="{{ old('modelo', $vehiculo->modelo ?? '') }}"
        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
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


<input
    type="text"
    name="color"
    placeholder="Color"
    value="{{ old('color', $vehiculo->color ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<input
    type="text"
    name="clase_vehiculo"
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
    placeholder="CC"
    value="{{ old('cc', $vehiculo->cc ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<input
    type="text"
    name="combustible"
    placeholder="Combustible"
    value="{{ old('combustible', $vehiculo->combustible ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

<select
    name="transmision"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">

    <option value="">Transmisión</option>

    <option value="Manual"
        @selected(old('transmision', $vehiculo->transmision ?? '') == 'Manual')>
        Manual
    </option>

    <option value="Automática"
        @selected(old('transmision', $vehiculo->transmision ?? '') == 'Automática')>
        Automática
    </option>

</select>

<input
    type="number"
    name="kilometraje"
    placeholder="Kilometraje"
    value="{{ old('kilometraje', $vehiculo->kilometraje ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">


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
<input
    type="text"
    name="ciudad_matricula"
    placeholder="Ciudad Matrícula"
    value="{{ old('ciudad_matricula', $vehiculo->ciudad_matricula ?? '') }}"
    class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3">
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
