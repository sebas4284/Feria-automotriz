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
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\RifaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\EstrategiaController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\PushSubscriptionController;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isAsesor() ? 'leads.index' : 'dashboard');
});

Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)->middleware(['auth', 'role:admin,concesionario,staff'])->name('dashboard');

Route::resource(
    'concesionarios',
    ConcesionarioController::class
)->middleware(['auth', 'role:admin']);

Route::resource('ventas', VentaController::class)
    ->middleware(['auth', 'role:admin,concesionario']);

//Asesores comerciales
Route::resource('asesores', AsesorComercialController::class)
    ->parameters(['asesores' => 'asesor'])
    ->middleware(['auth', 'role:admin,concesionario']);

//Rifa / Experiencia
Route::get('/rifa', [RifaController::class, 'index'])
    ->name('rifa.index')
    ->middleware(['auth', 'role:admin,staff']);

//Turnos de llegada de concesionarios
Route::get('/turnos', [TurnoController::class, 'index'])
    ->name('turnos.index')
    ->middleware(['auth', 'role:admin,staff']);

Route::get('/turnos/pantalla', [TurnoController::class, 'pantalla'])
    ->name('turnos.pantalla')
    ->middleware(['auth', 'role:admin,staff']);

Route::post('/turnos/{concesionario}/check-in', [TurnoController::class, 'checkIn'])
    ->name('turnos.check-in')
    ->middleware(['auth', 'role:admin']);

Route::delete('/turnos/{concesionario}/check-in', [TurnoController::class, 'checkOut'])
    ->name('turnos.check-out')
    ->middleware(['auth', 'role:admin']);

Route::post('/turnos/rotar', [TurnoController::class, 'rotar'])
    ->name('turnos.rotar')
    ->middleware(['auth', 'role:admin,staff']);

//Clientes
Route::resource('clientes', ClienteController::class)
    ->middleware(['auth', 'role:admin,concesionario']);
//Vehiculos
Route::resource('vehiculos', VehiculoController::class)
    ->middleware(['auth', 'role:admin,concesionario,asesor']);

Route::get('/vehiculos/{vehiculo}/ficha', [VehiculoController::class, 'ficha'])
    ->name('vehiculos.ficha')
    ->middleware(['auth', 'role:admin,concesionario,asesor']);

//Catálogos (marcas, ciudades, colores, combustibles editables desde admin)
Route::get('/catalogos/{tipo}', [CatalogoController::class, 'index'])
    ->name('catalogos.index')
    ->middleware(['auth', 'role:admin']);

Route::post('/catalogos/{tipo}', [CatalogoController::class, 'store'])
    ->name('catalogos.store')
    ->middleware(['auth', 'role:admin']);

Route::delete('/catalogos/{catalogo}', [CatalogoController::class, 'destroy'])
    ->name('catalogos.destroy')
    ->middleware(['auth', 'role:admin']);

//Leads
Route::resource('leads', LeadController::class)
    ->only(['index', 'show', 'edit', 'update', 'destroy'])
    ->middleware(['auth', 'role:admin,concesionario,asesor']);

Route::patch('leads/{lead}/reassign', [LeadController::class, 'reassign'])
    ->name('leads.reassign')
    ->middleware(['auth', 'role:admin,concesionario,asesor']);

Route::patch('leads/{lead}/assign-asesor', [LeadController::class, 'assignAsesor'])
    ->name('leads.assign-asesor')
    ->middleware(['auth', 'role:admin,concesionario,asesor']);

//Usuarios (administración de cuentas)
Route::resource('usuarios', UserController::class)
    ->except(['show'])
    ->middleware(['auth', 'role:admin']);

//Estadísticas
Route::get('/estadisticas', [EstadisticasController::class, 'index'])
    ->name('estadisticas.index')
    ->middleware(['auth', 'role:admin,concesionario']);

//Estrategia de ventas
Route::get('/estrategia', [EstrategiaController::class, 'index'])
    ->name('estrategia.index')
    ->middleware(['auth', 'role:admin']);

//Autenticación
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Notificaciones push
Route::middleware('auth')->group(function () {
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
});

require __DIR__.'/auth.php';
