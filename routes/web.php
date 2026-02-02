<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;

// Página pública
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas de autenticación (solo para invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Cerrar sesión (solo autenticados)
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Rutas protegidas para admin
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Citas
    Route::get('/citas', function () {
        return view('admin.citas.index');
    })->name('citas.index');
    Route::get('/citas/crear', function () {
        return view('admin.citas.create');
    })->name('citas.create');
    
    // Clientes
    Route::get('/clientes', function () {
        return view('admin.clientes.index');
    })->name('clientes.index');
    
    // Servicios
    Route::resource('servicios', \App\Http\Controllers\Admin\ServicioController::class);
    
    // Empleados
    Route::get('/empleados', function () {
        return view('admin.empleados.index');
    })->name('empleados.index');
    
    // Usuarios
    Route::get('/usuarios', function () {
        return view('admin.usuarios.index');
    })->name('usuarios.index');
    
    // Mis Citas (para estilistas)
    Route::get('/mis-citas', function () {
        return view('admin.mis-citas.index');
    })->name('mis-citas');
});

// Ruta para clientes autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/mi-cuenta', function () {
        return 'Mi Cuenta - Próximamente';
    })->name('mi-cuenta');
});
