<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\CitaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\CampanaController;
use App\Http\Controllers\Admin\ComisionController;
use App\Http\Controllers\Admin\ReporteController;

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
    Route::get('/citas/crear', [CitaController::class, 'create'])->name('citas.create');
    Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
    Route::get('/citas/calendario', [CitaController::class, 'calendario'])->name('citas.calendario');
    Route::get('/citas/calendario/datos', [CitaController::class, 'calendarDatos'])->name('citas.calendarDatos');
    Route::get('/citas/{cita}', [CitaController::class, 'show'])->name('citas.show');
    Route::get('/citas/{cita}/editar', [CitaController::class, 'edit'])->name('citas.edit');
    Route::put('/citas/{cita}', [CitaController::class, 'update'])->name('citas.update');
    
    // Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}/historial', [ClienteController::class, 'show'])->name('clientes.show');
    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::post('/clientes/{cliente}/visita', [ClienteController::class, 'storeCita'])->name('clientes.storeCita');
    
    // Servicios
    Route::resource('servicios', \App\Http\Controllers\Admin\ServicioController::class);
    
    // Campañas
    Route::get('/campanas', [CampanaController::class, 'index'])->name('campanas.index');
    Route::get('/campanas/crear', [CampanaController::class, 'create'])->name('campanas.create');
    Route::post('/campanas', [CampanaController::class, 'store'])->name('campanas.store');
    Route::get('/campanas/{campana}/editar', [CampanaController::class, 'edit'])->name('campanas.edit');
    Route::put('/campanas/{campana}', [CampanaController::class, 'update'])->name('campanas.update');

    // Comisiones
    Route::get('/comisiones', [ComisionController::class, 'index'])->name('comisiones.index');
    Route::put('/comisiones/{servicio}', [ComisionController::class, 'update'])->name('comisiones.update');

    // Reportes
    Route::get('/reportes/comisiones', [ReporteController::class, 'comisiones'])->name('reportes.comisiones');
    Route::get('/reportes/comisiones/pdf', [ReporteController::class, 'comisionesPdf'])->name('reportes.comisiones.pdf');
    Route::get('/reportes/comisiones/excel', [ReporteController::class, 'comisionesExcel'])->name('reportes.comisiones.excel');

    // Empleados
    Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
    Route::get('/empleados/crear', [EmpleadoController::class, 'create'])->name('empleados.create');
    Route::post('/empleados', [EmpleadoController::class, 'store'])->name('empleados.store');
    Route::get('/empleados/{empleado}', [EmpleadoController::class, 'show'])->name('empleados.show');
    Route::get('/empleados/{empleado}/editar', [EmpleadoController::class, 'edit'])->name('empleados.edit');
    Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');
    
    // Usuarios
    Route::get('/usuarios', function () {
        return view('admin.usuarios.index');
    })->name('usuarios.index');
    
    // Mis Citas (para estilistas)
    Route::get('/mis-citas', function () {
        return view('admin.mis-citas.index');
    })->name('mis-citas');

    // Inventario (solo admin)
    Route::middleware('role:admin')->prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index');

        Route::get('/productos', [InventarioController::class, 'productos'])->name('productos');
        Route::get('/productos/crear', [InventarioController::class, 'createProducto'])->name('productos.create');
        Route::post('/productos', [InventarioController::class, 'storeProducto'])->name('productos.store');
        Route::get('/productos/{producto}/editar', [InventarioController::class, 'editProducto'])->name('productos.edit');
        Route::put('/productos/{producto}', [InventarioController::class, 'updateProducto'])->name('productos.update');

        Route::get('/entradas', [InventarioController::class, 'entradas'])->name('entradas');
        Route::get('/entradas/crear', [InventarioController::class, 'createEntrada'])->name('entradas.create');
        Route::post('/entradas', [InventarioController::class, 'storeEntrada'])->name('entradas.store');

        Route::get('/salidas', [InventarioController::class, 'salidas'])->name('salidas');
        Route::get('/salidas/crear', [InventarioController::class, 'createSalida'])->name('salidas.create');
        Route::post('/salidas', [InventarioController::class, 'storeSalida'])->name('salidas.store');

        Route::get('/analisis', [InventarioController::class, 'analisis'])->name('analisis');

        Route::get('/importar', [InventarioController::class, 'showImport'])->name('importar');
        Route::post('/importar', [InventarioController::class, 'import'])->name('importar.store');
    });
});

// Ruta para clientes autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/mi-cuenta', function () {
        return 'Mi Cuenta - Próximamente';
    })->name('mi-cuenta');
});
