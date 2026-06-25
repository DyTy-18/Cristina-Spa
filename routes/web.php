<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\Admin\CitaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\CampanaController;
use App\Http\Controllers\Admin\ComisionController;
use App\Http\Controllers\Admin\EmpleadoComisionController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DescuentoController;
use App\Http\Controllers\Admin\PaqueteController;
use App\Http\Controllers\Admin\ContratoPaqueteController;
use App\Http\Controllers\Admin\RecomendacionController;
use App\Http\Controllers\RecomendacionPublicController;
use App\Http\Controllers\Admin\AlertaStockController;
use App\Http\Controllers\Admin\MisCitasController;
use App\Http\Controllers\Admin\SucursalController;
use App\Http\Controllers\Admin\MigracionSucursalController;
use App\Http\Controllers\Admin\IngresosController;
use App\Http\Controllers\Admin\GastosController;

// Página pública
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/nosotros', function () {
    return view('nosotros');
})->name('nosotros');

// Leads desde formulario público (sin auth)
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');

// Formulario de recomendación (público, sin auth)
Route::get('/r/{token}', [RecomendacionPublicController::class, 'show'])->name('recomendacion.form');
Route::post('/r/{token}', [RecomendacionPublicController::class, 'store'])->name('recomendacion.store');
Route::get('/r/{token}/gracias', function () {
    return view('recomendacion.gracias');
})->name('recomendacion.gracias');

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
Route::middleware(['auth', 'sucursal'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Ingresos
    Route::middleware('role:admin')->get('/ingresos', [IngresosController::class, 'index'])->name('ingresos.index');

    // Gastos
    Route::middleware('role:admin')->group(function () {
        Route::get('/gastos', [GastosController::class, 'index'])->name('gastos.index');
        Route::get('/gastos/servicios-empleado', [GastosController::class, 'serviciosEmpleado'])->name('gastos.serviciosEmpleado');
        Route::post('/gastos/verificar-password', [GastosController::class, 'verificarPassword'])->name('gastos.verificarPassword');
        Route::post('/gastos/gasto', [GastosController::class, 'storeGasto'])->name('gastos.storeGasto');
        Route::delete('/gastos/gasto/{gasto}', [GastosController::class, 'destroyGasto'])->name('gastos.destroyGasto');
        Route::post('/gastos/pago', [GastosController::class, 'storePago'])->name('gastos.storePago');
        Route::delete('/gastos/pago/{pago}', [GastosController::class, 'destroyPago'])->name('gastos.destroyPago');
    });
    
    // Citas
    Route::get('/citas', [CitaController::class, 'index'])->name('citas.index');
    Route::get('/citas/crear', [CitaController::class, 'create'])->name('citas.create');
    Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
    Route::get('/citas/calendario', [CitaController::class, 'calendario'])->name('citas.calendario');
    Route::get('/citas/calendario/datos', [CitaController::class, 'calendarDatos'])->name('citas.calendarDatos');
    Route::get('/citas/{cita}', [CitaController::class, 'show'])->name('citas.show');
    Route::get('/citas/{cita}/informe', [CitaController::class, 'informe'])->name('citas.informe');
    Route::get('/citas/{cita}/editar', [CitaController::class, 'edit'])->name('citas.edit');
    Route::put('/citas/{cita}', [CitaController::class, 'update'])->name('citas.update');
    Route::patch('/citas/{cita}/estado', [CitaController::class, 'updateEstado'])->name('citas.estado');
    Route::post('/citas/{cita}/verificar-edicion', [CitaController::class, 'verificarEdicion'])->name('citas.verificarEdicion');
    
    // Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}/historial', [ClienteController::class, 'show'])->name('clientes.show');
    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::post('/clientes/{cliente}/visita', [ClienteController::class, 'storeCita'])->name('clientes.storeCita');
    Route::patch('/clientes/{cliente}/citas/{cita}/estado', [ClienteController::class, 'updateCitaEstado'])->name('clientes.cita.estado');
    Route::post('/clientes/{cliente}/recomendacion/generar', [RecomendacionController::class, 'generar'])->name('clientes.recomendacion.generar');
    Route::get('/clientes/ocultos', [ClienteController::class, 'ocultos'])->name('clientes.ocultos');
    Route::post('/clientes/{cliente}/ocultar', [ClienteController::class, 'toggleOculto'])->name('clientes.toggleOculto');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::delete('/recomendacion-links/{link}', [RecomendacionController::class, 'destroy'])->name('recomendacion.link.destroy');
    
    // Servicios
    Route::resource('servicios', \App\Http\Controllers\Admin\ServicioController::class);

    // Materiales de servicio
    Route::prefix('servicios/{servicio}/materiales')->name('servicios.materiales.')->group(function () {
        Route::post('/', [\App\Http\Controllers\Admin\ServicioMaterialController::class, 'store'])->name('store');
        Route::put('/{material}', [\App\Http\Controllers\Admin\ServicioMaterialController::class, 'update'])->name('update');
        Route::delete('/{material}', [\App\Http\Controllers\Admin\ServicioMaterialController::class, 'destroy'])->name('destroy');
    });

    // Campañas
    Route::middleware('permission:ver campanas')->group(function () {
        Route::get('/campanas', [CampanaController::class, 'index'])->name('campanas.index');
        Route::middleware('permission:gestionar campanas')->group(function () {
            Route::get('/campanas/crear', [CampanaController::class, 'create'])->name('campanas.create');
            Route::post('/campanas', [CampanaController::class, 'store'])->name('campanas.store');
            Route::get('/campanas/{campana}/editar', [CampanaController::class, 'edit'])->name('campanas.edit');
            Route::put('/campanas/{campana}', [CampanaController::class, 'update'])->name('campanas.update');
        });
    });

    // Comisiones
    Route::middleware('permission:ver comisiones')->group(function () {
        Route::get('/comisiones', [ComisionController::class, 'index'])->name('comisiones.index');
        Route::put('/comisiones/{servicio}', [ComisionController::class, 'update'])->name('comisiones.update');

        // Comisiones personales por empleado
        Route::get('/empleados/{empleado}/comisiones', [EmpleadoComisionController::class, 'index'])->name('empleados.comisiones.index');
        Route::put('/empleados/{empleado}/comisiones/{servicio}', [EmpleadoComisionController::class, 'update'])->name('empleados.comisiones.update');
    });

    // Reportes
    Route::middleware('permission:ver reportes')->group(function () {
        Route::get('/reportes/comisiones', [ReporteController::class, 'comisiones'])->name('reportes.comisiones');
        Route::get('/reportes/comisiones/pdf', [ReporteController::class, 'comisionesPdf'])->name('reportes.comisiones.pdf');
        Route::get('/reportes/comisiones/excel', [ReporteController::class, 'comisionesExcel'])->name('reportes.comisiones.excel');

        Route::get('/reportes/clientes', [ReporteController::class, 'clientes'])->name('reportes.clientes');
        Route::get('/reportes/clientes/pdf', [ReporteController::class, 'clientesPdf'])->name('reportes.clientes.pdf');
        Route::get('/reportes/clientes/excel', [ReporteController::class, 'clientesExcel'])->name('reportes.clientes.excel');
    });

    // Empleados
    Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
    Route::get('/empleados/crear', [EmpleadoController::class, 'create'])->name('empleados.create');
    Route::post('/empleados', [EmpleadoController::class, 'store'])->name('empleados.store');
    Route::get('/empleados/{empleado}', [EmpleadoController::class, 'show'])->name('empleados.show');
    Route::get('/empleados/{empleado}/editar', [EmpleadoController::class, 'edit'])->name('empleados.edit');
    Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');
    
    // Usuarios y Roles (solo admin)
    Route::middleware('permission:gestionar usuarios')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
        Route::get('/roles/{rol}/editar', [RolController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{rol}', [RolController::class, 'update'])->name('roles.update');

        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::get('/logs/exportar-pdf', [ActivityLogController::class, 'exportPdf'])->name('logs.export-pdf');
    });
    
    // Paquetes de servicios (solo admin)
    Route::middleware('role:admin')->prefix('paquetes')->name('paquetes.')->group(function () {
        Route::get('/', [PaqueteController::class, 'index'])->name('index');
        Route::post('/', [PaqueteController::class, 'store'])->name('store');
        Route::put('/{paquete}', [PaqueteController::class, 'update'])->name('update');
        Route::put('/{paquete}/toggle', [PaqueteController::class, 'toggle'])->name('toggle');
        Route::delete('/{paquete}', [PaqueteController::class, 'destroy'])->name('destroy');
        Route::get('/{paquete}/servicios', [PaqueteController::class, 'serviciosJson'])->name('servicios');

        // Niveles
        Route::post('/niveles', [PaqueteController::class, 'storeNivel'])->name('niveles.store');
        Route::put('/niveles/{nivel}', [PaqueteController::class, 'updateNivel'])->name('niveles.update');
        Route::delete('/niveles/{nivel}', [PaqueteController::class, 'destroyNivel'])->name('niveles.destroy');
    });

    // Contratos de paquetes
    Route::middleware('role:admin')->prefix('contratos')->name('contratos.')->group(function () {
        Route::get('/', [ContratoPaqueteController::class, 'index'])->name('index');
        Route::post('/', [ContratoPaqueteController::class, 'store'])->name('store');
        Route::get('/{contrato}', [ContratoPaqueteController::class, 'show'])->name('show');
        Route::delete('/{contrato}', [ContratoPaqueteController::class, 'destroy'])->name('destroy');
        Route::put('/{contrato}/estado', [ContratoPaqueteController::class, 'cambiarEstado'])->name('estado');
        Route::put('/{contrato}/servicios/{servicio}/toggle', [ContratoPaqueteController::class, 'toggleServicio'])->name('servicios.toggle');
        Route::put('/{contrato}/pagos/{pago}/pagar', [ContratoPaqueteController::class, 'pagarCuota'])->name('pagos.pagar');
        Route::post('/{contrato}/pagos', [ContratoPaqueteController::class, 'addCuota'])->name('pagos.store');
    });

    // Descuentos & Cupones (solo admin)
    Route::middleware('role:admin')->prefix('descuentos')->name('descuentos.')->group(function () {
        Route::get('/', [DescuentoController::class, 'index'])->name('index');

        Route::post('/programados', [DescuentoController::class, 'storeProgramado'])->name('programados.store');
        Route::put('/programados/{descuento}/toggle', [DescuentoController::class, 'toggleProgramado'])->name('programados.toggle');
        Route::delete('/programados/{descuento}', [DescuentoController::class, 'destroyProgramado'])->name('programados.destroy');

        Route::post('/cupones', [DescuentoController::class, 'storeCupon'])->name('cupones.store');
        Route::get('/cupones/verificar', [DescuentoController::class, 'verificarCupon'])->name('cupones.verificar');
        Route::put('/cupones/{cupon}/toggle', [DescuentoController::class, 'toggleCupon'])->name('cupones.toggle');
        Route::delete('/cupones/{cupon}', [DescuentoController::class, 'destroyCupon'])->name('cupones.destroy');
    });

    // Leads (admin y secretario)
    Route::middleware('role:admin|secretario')->group(function () {
        Route::get('/leads', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::put('/leads/{lead}', [AdminLeadController::class, 'update'])->name('leads.update');
    });

    // Alertas de Stock
    Route::patch('alertas-stock/{alerta}/leer', [AlertaStockController::class, 'leer'])
         ->name('alertas-stock.leer');

    // Mis Citas (para estilistas)
    Route::get('/mis-citas', [MisCitasController::class, 'index'])->name('mis-citas');
    Route::get('/mis-citas/{cita}/consumo', [MisCitasController::class, 'consumo'])->name('mis-citas.consumo');
    Route::post('/mis-citas/{cita}/consumo', [MisCitasController::class, 'guardarConsumo'])->name('mis-citas.consumo.store');

    // Sucursales (solo admin)
    Route::middleware('role:admin|developer')->prefix('sucursales')->name('sucursales.')->group(function () {
        Route::get('/', [SucursalController::class, 'index'])->name('index');
        Route::get('/crear', [SucursalController::class, 'create'])->name('create');
        Route::post('/', [SucursalController::class, 'store'])->name('store');
        Route::get('/{sucursal}/editar', [SucursalController::class, 'edit'])->name('edit');
        Route::put('/{sucursal}', [SucursalController::class, 'update'])->name('update');
        Route::get('/migracion', [MigracionSucursalController::class, 'index'])->name('migracion');
        Route::post('/migracion', [MigracionSucursalController::class, 'procesar'])->name('migracion.procesar');
    });
    Route::post('/sucursal/cambiar', [SucursalController::class, 'cambiar'])->name('sucursal.cambiar');

    // Inventario (admin, cajero, finanzas)
    Route::middleware('permission:ver inventario')->prefix('inventario')->name('inventario.')->group(function () {
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

        Route::delete('/limpiar', [InventarioController::class, 'limpiar'])->name('limpiar');
    });
});

// Ruta para clientes autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/mi-cuenta', function () {
        return 'Mi Cuenta - Próximamente';
    })->name('mi-cuenta');
});
