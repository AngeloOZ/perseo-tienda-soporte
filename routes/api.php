<?php

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
});