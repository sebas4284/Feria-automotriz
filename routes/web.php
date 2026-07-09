<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;

use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ConcesionarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AsesorComercialController;
use App\Http\Controllers\RifaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EstadisticasController;

Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)->middleware('auth')->name('dashboard');

Route::resource(
    'concesionarios',
    ConcesionarioController::class
)->middleware(['auth', 'role:admin']);

Route::resource('ventas', VentaController::class)
    ->middleware('auth');

//Asesores comerciales
Route::resource('asesores', AsesorComercialController::class)
    ->parameters(['asesores' => 'asesor'])
    ->middleware('auth');

//Rifa / Experiencia
Route::get('/rifa', [RifaController::class, 'index'])
    ->name('rifa.index')
    ->middleware(['auth', 'role:admin']);

//Clientes
Route::resource('clientes', ClienteController::class)
    ->middleware('auth');
//Vehiculos
Route::resource('vehiculos', VehiculoController::class)
    ->middleware('auth');

//Leads
Route::resource('leads', LeadController::class)
    ->only(['index', 'show', 'edit', 'update', 'destroy'])
    ->middleware('auth');

Route::patch('leads/{lead}/reassign', [LeadController::class, 'reassign'])
    ->name('leads.reassign')
    ->middleware('auth');

//Usuarios (administración de cuentas)
Route::resource('usuarios', UserController::class)
    ->except(['show'])
    ->middleware(['auth', 'role:admin']);

//Estadísticas
Route::get('/estadisticas', [EstadisticasController::class, 'index'])
    ->name('estadisticas.index')
    ->middleware('auth');

//Autenticación
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
