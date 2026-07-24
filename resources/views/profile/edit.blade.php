@extends('layouts.app')

@section('content')

<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl lg:text-3xl font-bold">Mi perfil</h1>
        <p class="text-gray-400 mt-1 text-sm">Datos de tu cuenta y contraseña</p>
    </div>

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
