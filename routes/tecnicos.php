<?php

use App\Http\Controllers\CobrosController;
use App\Http\Controllers\FacturasLicenciasRenovarController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('dev')->group(function () {

    // Route::get('/estado-firma', function () {
    //     return "ok";
    // });

    // Route::get('/licencias', [FacturasLicenciasRenovarController::class, 'generar_facturas_renovacion']);

    Route::get('/prueba', [FacturasLicenciasRenovarController::class, 'index']);

    Route::get('/csv', [CobrosController::class, 'csv']);
    Route::post('/csv', [CobrosController::class, 'csv_post'])->name('csv_post');
});
