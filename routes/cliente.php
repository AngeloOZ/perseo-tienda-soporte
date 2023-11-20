<?php

use App\Http\Controllers\clientesController;
use App\Http\Controllers\cotizarController;
use Illuminate\Support\Facades\Route;

Route::prefix('cliente')->group(function () {
    Route::get('/login', [clientesController::class, 'login'])->name('clientes.login');
    Route::post('/login', [clientesController::class, 'post_login'])->name('clientes.post_login');

    Route::group(['middleware' => 'cliente'], function () {
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



Route::group(['middleware' => 'tecnico', 'prefix' => 'cotizaciones'], function () {
    Route::prefix('detalles')->group(function () {
        Route::get('/listado', [cotizarController::class, 'listado'])->name('detalles.listado');
        Route::get('/crear', [cotizarController::class, 'crear'])->name('detalles.crear');
        Route::post('/guardar', [cotizarController::class, 'guardar'])->name('detalles.guardar');
        Route::get('/editar/{detalles}', [cotizarController::class, 'editar'])->name('detalles.editar');
        Route::put('/actualizar/{detalles}', [cotizarController::class, 'actualizar'])->name('detalles.actualizar');
        Route::delete('/eliminar/{detalles}', [cotizarController::class, 'eliminar'])->name('detalles.eliminar');
    });

    Route::prefix('cotizar')->group(function () {
        Route::get('/listado', [cotizarController::class, 'listadoCotizaciones'])->name('listadoCotizaciones.listado');

        Route::get('/crear/{prospecto}', [cotizarController::class, 'crearPlantilla'])->name('cotizarPlantilla1.index');
        Route::post('/guardar', [cotizarController::class, 'guardarPlantilla'])->name('descargarPlantilla.index');

        Route::get('/editar/{cotizacion}', [cotizarController::class, 'editarCotizaciones'])->name('cotizarPlantilla.editar');
        Route::put('/actualizar/{cotizacion}', [cotizarController::class, 'actualizarCotizaciones'])->name('actualizarCotizacion.index');
        Route::post('/recuperar-precio', [cotizarController::class, 'recuperarPrecio'])->name('recuperarPrecio');

        Route::delete('/eliminar/{cotizacion}', [cotizarController::class, 'eliminarCotizaciones'])->name('eliminarCotizaciones.eliminar');
    });
});
