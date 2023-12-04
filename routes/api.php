<?php

use App\Http\Controllers\CobrosController;
use App\Http\Controllers\FacturasLicenciasRenovarController;
use App\Http\Controllers\MacroAPIController;
use App\Http\Controllers\SoporteApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/soportes', [SoporteApiController::class, 'obtener_soporte_powerbi']);

    Route::get('/macro-ventas/{secuencia}/{distribuidor?}', [MacroAPIController::class, 'obtener_macro_ventas']);


    Route::group(['prefix' => 'licenciador'], function () {

        Route::post('/emitir-factura', [FacturasLicenciasRenovarController::class, 'generar_factura_licenciador'])->name('licenciador.emitir-factura');

        Route::get('/emitir-factura', [FacturasLicenciasRenovarController::class, 'generar_factura_licenciador'])->name('licenciador.emitir-factura2');
    });

    Route::group(['prefix' => 'cobros'], function () {

        Route::post('/verificar-estado', [CobrosController::class, 'verificar_estado_cobro'])->name('api.cobros.verificar-estado');
    });
});
