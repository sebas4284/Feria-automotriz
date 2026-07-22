@extends('layouts.app')

@section('content')

<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl lg:text-3xl font-bold">Mi perfil</h1>
        <p class="text-gray-400 mt-1 text-sm">Datos de tu cuenta y contraseña</p>
    </div>

    @if (session('status') === 'must-change-password')
        <div class="bg-amber-500/10 border border-amber-500/50 rounded-xl p-4 text-amber-400 text-sm">
            Por seguridad debes cambiar tu contraseña antes de continuar usando el sistema.
        </div>
    @endif

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        @include('profile.partials.update-password-form')
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        @include('profile.partials.delete-user-form')
    </div>

</div>

@endsection
