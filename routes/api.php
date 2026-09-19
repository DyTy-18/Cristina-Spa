<?php

use App\Http\Controllers\Api\WppQueryController;
use App\Http\Controllers\Api\WppWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API para sincronización con el servidor WPP (WhatsApp)
|--------------------------------------------------------------------------
|
| Todas las rutas requieren el mismo Bearer token que Laravel usa para el
| push saliente (WPPCONNECT_TOKEN, ver config/services.php).
|
| Saliente (WPP → consulta): GET /wpp/citas
| Entrante (WPP → Laravel):  cambios de estado, creación y reprogramación
| de citas originados por el bot.
|
*/
Route::middleware('wpp.auth')->prefix('wpp')->name('api.wpp.')->group(function () {
    Route::get('/citas', [WppQueryController::class, 'citas'])->name('citas.index');

    Route::post('/citas', [WppWebhookController::class, 'store'])->name('citas.store');
    Route::patch('/citas/{cita}', [WppWebhookController::class, 'reprogramar'])->name('citas.reprogramar');
    Route::patch('/citas/{cita}/estado', [WppWebhookController::class, 'updateEstado'])->name('citas.estado');
});
