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
use App\Http\Controllers\PorteriaController;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->isAsesor()) {
        return redirect()->route('leads.index');
    }

    if (auth()->user()->isPorteria()) {
        return redirect()->route('porteria.index');
    }

    return redirect()->route('dashboard');
});

Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)->middleware(['auth', 'role:admin,concesionario,staff'])->name('dashboard');

Route::resource(
    'concesionarios',
    ConcesionarioController::class
)->middleware(['auth', 'role:admin']);

Route::get('/ventas/eliminadas', [VentaController::class, 'eliminadas'])
    ->name('ventas.eliminadas')
    ->middleware(['auth', 'role:admin']);

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

Route::get('/turnos/pantalla/llegadas', [TurnoController::class, 'pantallaLlegadas'])
    ->name('turnos.pantalla-llegadas')
    ->middleware(['auth', 'role:admin,staff']);

Route::post('/turnos/{concesionario}/check-in', [TurnoController::class, 'checkIn'])
    ->name('turnos.check-in')
    ->middleware(['auth', 'role:admin,staff']);

Route::delete('/turnos/{concesionario}/check-in', [TurnoController::class, 'checkOut'])
    ->name('turnos.check-out')
    ->middleware(['auth', 'role:admin,staff']);

Route::post('/turnos/{concesionario}/saltar', [TurnoController::class, 'saltarTurno'])
    ->name('turnos.saltar')
    ->middleware(['auth', 'role:admin,staff']);

Route::post('/turnos/{concesionario}/deshacer-asignacion', [TurnoController::class, 'deshacerAsignacion'])
    ->name('turnos.deshacer-asignacion')
    ->middleware(['auth', 'role:admin,staff']);

Route::post('/turnos/pendiente/{cliente}/quitar', [TurnoController::class, 'quitarPendiente'])
    ->name('turnos.quitar-pendiente')
    ->middleware(['auth', 'role:admin,staff']);

Route::post('/turnos/asignar-cliente', [TurnoController::class, 'asignarCliente'])
    ->name('turnos.asignar-cliente')
    ->middleware(['auth', 'role:admin,staff']);

//Portería (check-in de vehículos en la entrada de la feria)
Route::get('/porteria', [PorteriaController::class, 'index'])
    ->name('porteria.index')
    ->middleware(['auth', 'role:admin,porteria']);

Route::post('/porteria/{vehiculo}/ingreso', [PorteriaController::class, 'marcarIngreso'])
    ->name('porteria.ingreso')
    ->middleware(['auth', 'role:admin,porteria']);

Route::delete('/porteria/{vehiculo}/ingreso', [PorteriaController::class, 'quitarIngreso'])
    ->name('porteria.quitar-ingreso')
    ->middleware(['auth', 'role:admin,porteria']);

//Clientes
Route::resource('clientes', ClienteController::class)
    ->only(['index', 'create', 'store', 'show'])
    ->middleware(['auth', 'role:admin,concesionario,staff']);

Route::resource('clientes', ClienteController::class)
    ->only(['edit', 'update', 'destroy'])
    ->middleware(['auth', 'role:admin,concesionario']);
//Vehiculos
Route::get('/vehiculos/eliminados', [VehiculoController::class, 'eliminados'])
    ->name('vehiculos.eliminados')
    ->middleware(['auth', 'role:admin']);

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
