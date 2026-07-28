@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto" x-data="{ rol: '{{ old('rol', $usuario->rol) }}' }">

    <h1 class="text-3xl font-bold mb-8">Editar Usuario</h1>

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

    <form method="POST" action="{{ route('usuarios.update', $usuario) }}"
        class="bg-gray-900 border border-gray-800 rounded-3xl p-8">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm text-gray-400 mb-2">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $usuario->name) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $usuario->email) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-2">Contraseña</label>
                <input type="password" name="password" placeholder="Dejar en blanco para no cambiar"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-2">Rol</label>
                <select name="rol" x-model="rol"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
                    <option value="concesionario" @selected(old('rol', $usuario->rol) === 'concesionario')>Concesionario</option>
                    <option value="asesor" @selected(old('rol', $usuario->rol) === 'asesor')>Asesor</option>
                    <option value="staff" @selected(old('rol', $usuario->rol) === 'staff')>Staff (Rifa y Turnos)</option>
                    <option value="porteria" @selected(old('rol', $usuario->rol) === 'porteria')>Portería (check-in de vehículos)</option>
                    <option value="admin" @selected(old('rol', $usuario->rol) === 'admin')>Admin</option>
                </select>
            </div>

            <div x-show="rol === 'concesionario'">
                <label class="block text-sm text-gray-400 mb-2">Concesionario</label>
                <select name="concesionario_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
                    <option value="">Selecciona uno</option>
                    @foreach($concesionarios as $c)
                        <option value="{{ $c->id }}" @selected((int) old('concesionario_id', $usuario->concesionario_id) === $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="rol === 'asesor'">
                <label class="block text-sm text-gray-400 mb-2">Asesor comercial</label>
                <select name="asesor_comercial_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl p-3 focus:outline-none focus:border-blue-500">
                    <option value="">Selecciona uno</option>
                    @foreach($asesoresComerciales as $a)
                        <option value="{{ $a->id }}" @selected((int) old('asesor_comercial_id', $usuario->asesor_comercial_id) === $a->id)>{{ $a->nombre }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-2">El asesor debe existir primero en la sección Asesores.</p>
            </div>

        </div>

        <div class="mt-10 flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-xl font-medium transition">
                Actualizar Usuario
            </button>
            <a href="{{ route('usuarios.index') }}" class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-xl transition">
                Cancelar
            </a>
        </div>

    </form>

</div>

@endsection
