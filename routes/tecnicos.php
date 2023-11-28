<?php

use App\Http\Controllers\FacturasLicenciasRenovarController;
use App\Http\Controllers\PruebasController;
use Illuminate\Support\Facades\Route;

Route::prefix('dev')->group(function () {

    // Route::get('/estado-firma', function () {
    //     return "ok";
    // });

    // Route::get('/licencias', [FacturasLicenciasRenovarController::class, 'generar_facturas_renovacion']);

    Route::get('/prueba', [FacturasLicenciasRenovarController::class, 'index']);
    Route::get('/pdf', [PruebasController::class, 'word_to_pdf_python'])->name('pdf.python');
});
