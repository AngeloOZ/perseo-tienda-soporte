<?php

use App\Http\Controllers\actividadesController;
use App\Http\Controllers\clientesController;
use App\Http\Controllers\implementacionesDetallesController;
use App\Http\Controllers\implementacionesController;
use App\Http\Controllers\implementacionesDocumentosController;
use App\Http\Controllers\tecnicosController;


use Illuminate\Support\Facades\Route;

Route::prefix('cliente')->group(function () {

    Route::get('/login', [clientesController::class, 'login'])->name('clientes.login');
    Route::post('/login', [clientesController::class, 'post_login'])->name('clientes.post_login');

    Route::group(['middleware' => 'cliente'], function () {
        
        Route::get('/inicio', [clientesController::class, 'indexFront'])->name('clientesFront.index');
        Route::get('/implementaciones/{clientes}/{producto}/{implementacion}', [clientesController::class, 'implementacionesClientes'])->name('implementacionesClientes.ver');
        Route::post('/detallesIngresarClienteFechaInicio', [implementacionesDetallesController::class, 'ingresarClienteFecha'])->name('implementacionesDetalles.ingresarClienteFechaInicio');
        Route::post('/detallesIngresarClienteFechaFin', [implementacionesDetallesController::class, 'ingresarClienteFechaFin'])->name('implementacionesDetalles.ingresarClienteFechaFin');
        Route::post('/detallesRecuperarOtrosTemasClientes', [implementacionesDetallesController::class, 'recuperarOtrosTemasClientes'])->name('implementacionesDetalles.recuperarOtrosTemasClientes');
        Route::post('/detallesEnviarFechaImplementacion', [implementacionesController::class, 'enviarFechaImplementacion'])->name('implementaciones.enviarFechaImplementacion');
        Route::get('/listadoDocumentos', [clientesController::class, 'listadoDocumentos'])->name('listadoDocumentos.listado');
        Route::get('/descargar/{id}', [implementacionesDocumentosController::class, 'descargarDocumentos'])->name('documentosClientes.descargar');

        Route::get('/Cliente', [tecnicosController::class, 'vista404'])->name('clientes.404');
        Route::post('/SoporteTecnico', [actividadesController::class, 'soportetecnico'])->name('soportetecnico.listado');
        Route::get('/SoporteTecnico/listado', [actividadesController::class, 'indexSoporteTecnico'])->name('soportetecnico.index');
        Route::get('/Ver/{actividades}', [actividadesController::class, 'ver'])->name('soportetecnico.ver');
        Route::get('/detallesvideos/{producto}/{cliente}', [implementacionesController::class, 'videosClientes'])->name('implementaciones.videos.cliente');
        Route::get('/detallesprocesos/{producto}/{cliente}', [implementacionesController::class, 'procesosClientes'])->name('implementaciones.procesos.cliente');

        Route::post('/salirCliente', [clientesController::class, 'logout'])->name('clientes.logout');
        Route::post('/menuCliente', [clientesController::class, 'cambiarMenuCliente'])->name('cambiarMenuCliente');

        Route::get('/clienteListado', [clientesController::class, 'indexVista'])->name('sesiones.indexVistaCliente');
        Route::post('/listado', [clientesController::class, 'indexSesiones'])->name('sesiones.indexCliente');
        Route::get('/revision', [clientesController::class, 'verificar'])->name('sesiones.verificar');
        Route::post('/revision', [clientesController::class, 'guardarRevision'])->name('sesiones.ingresarRevision');
        Route::get('/sesionCliente/{sesiones}', [clientesController::class, 'sesionesVer'])->name('sesiones.ver');
        Route::post('/sesion/guardar', [clientesController::class, 'guardarCalificacion'])->name('sesiones.guardarcalificacion');


        Route::get('/cambiarCliente', [clientesController::class, 'cambiarClaveCliente'])->name('clientes.cambiarClaveCliente');
        Route::post('/guardarCliente', [clientesController::class, 'guardarClaveCliente'])->name('clientes.guardarClaveCliente');
    });
});
