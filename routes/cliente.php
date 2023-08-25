<?php

use App\Http\Controllers\clientesController;
use Illuminate\Support\Facades\Route;

Route::prefix('cliente')->group(function () {
    Route::get('/login', [clientesController::class, 'login'])->name('clientes.login');
    Route::post('/login', [clientesController::class, 'post_login'])->name('clientes.post_login');

    Route::group(['middleware' => 'cliente'], function () {
        // Revisados
        Route::get('/listado', [clientesController::class, 'indexVista'])->name('sesiones.indexVistaCliente');
        Route::post('/listado', [clientesController::class, 'indexSesiones'])->name('sesiones.indexCliente');
        Route::get('/sesion/{sesiones}', [clientesController::class, 'sesionesVer'])->name('sesiones.ver');
        Route::post('/sesion/guardar', [clientesController::class, 'guardarCalificacion'])->name('sesiones.guardarcalificacion');

        Route::get('/revision', [clientesController::class, 'verificar'])->name('sesiones.verificar');
        Route::post('/revision', [clientesController::class, 'guardarRevision'])->name('sesiones.ingresarRevision');
        
        Route::get('/cambiar', [clientesController::class, 'cambiarClaveCliente'])->name('clientes.cambiarClaveCliente');
        Route::post('/guardar', [clientesController::class, 'guardarClaveCliente'])->name('clientes.guardarClaveCliente');

        Route::post('/salir', [clientesController::class, 'logout'])->name('clientes.logout');
    });
});
