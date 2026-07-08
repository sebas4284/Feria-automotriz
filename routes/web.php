<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;

use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ConcesionarioController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MetaWebhookController;

// Webhook Meta Lead Ads — sin middleware auth ni CSRF (ver bootstrap/app.php)
Route::get('/webhook/meta', [MetaWebhookController::class, 'verify'])->name('webhook.meta.verify');
Route::post('/webhook/meta', [MetaWebhookController::class, 'handle'])->name('webhook.meta.handle');

Route::get(
    '/dashboard',
    [DashboardController::class, 'index']
)->middleware('auth')->name('dashboard');

Route::resource(
    'concesionarios',
    ConcesionarioController::class
)->middleware('auth');
Route::resource(
    'leads',
    LeadController::class
);

Route::resource('ventas', VentaController::class)
    ->middleware('auth');

//Clientes
Route::resource('clientes', ClienteController::class)
    ->middleware('auth');
//Vehiculos    
Route::resource('vehiculos', VehiculoController::class)
    ->middleware('auth');    
//Autenticación
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
