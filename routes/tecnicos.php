<?php

use App\Http\Controllers\CobrosController;
use App\Http\Controllers\FacturasLicenciasRenovarController;
use App\Http\Controllers\PruebasController;
use App\Http\Controllers\VerificarCobrosLotesController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('dev')->group(function () {

    // Route::get('/estado-firma', function () {
    //     return "ok";
    // });

    // Route::get('/licencias', [FacturasLicenciasRenovarController::class, 'generar_facturas_renovacion']);

    Route::get('/prueba', [FacturasLicenciasRenovarController::class, 'index']);

    Route::get('/cobros/listado', [VerificarCobrosLotesController::class, 'listar_cobros_lotes'])->name('pagos.lotes.list');
    Route::post('/cobros/listado', [VerificarCobrosLotesController::class, 'procesar_cobro_lotes'])->name('pagos.lotes.post');
    Route::post('/cobros/registro/lotes', [VerificarCobrosLotesController::class, 'registrar_cobro_sistema'])->name('cobros.registro.lotes');


    Route::get('/pdf', [PruebasController::class, 'word_to_pdf_python'])->name('pdf.python');
});
