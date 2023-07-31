<?php

use App\Http\Controllers\adminController;
use App\Http\Controllers\CiudadesController;
use App\Http\Controllers\CobrosController;
use App\Http\Controllers\ComisionesController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\CuponesController;
use App\Http\Controllers\FacturasController;
use App\Http\Controllers\FirmaController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\PruebaController;
use App\Http\Controllers\usuariosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',function(){
   return redirect()->route('auth.login'); 
});

Route::get('/login', function () {
    return view('auth.login');
})->name('auth.login');

Route::get("/fuera-de-servicio", function () {
    return view("errors.fuera_servicio");
});

Route::get('/referido/{id}', [usuariosController::class, 'validarVendedor'])->name('inicio');

Route::get('/facturacion/{id}', [usuariosController::class, 'datosFacturacion'])->name('inicio.facturacion');

Route::get('/estado-solicitud/{id}', [FirmaController::class, 'rastrearProceso'])->name('firma.estadosolicitud');

Route::get('/status/{cedula}', [FirmaController::class, 'validarProceso'])->name('validarestado');


Route::post('/ciudades', [CiudadesController::class, 'recuperarciudades'])->name('firma.recuperarciudades');
Route::post('/datos', [FirmaController::class, 'recuperardatos'])->name('firma.index');
Route::post('/email/datos', [adminController::class, 'verificarEmailCelular'])->name('admin.verificaremailcelular');
Route::post('/guardar', [FirmaController::class, 'guardar'])->name('firma.guardar');
Route::post('/reenviar/correo', [FirmaController::class, 'reenviar_correo'])->name('reenviar.correo');

Route::get('/{referido}/tienda', [FacturasController::class, 'listar_productos'])->name('tienda');
Route::get('/{referido}/tienda/resumen', [FacturasController::class, 'resumen_compra'])->name('tienda.checkout');
Route::get('/{referido}/tienda/finalizar-compra/{pago?}', [FacturasController::class, 'finalizar_compra'])->name('tienda.finalizar_compra');
Route::post('/registrar/compra',  [FacturasController::class, 'registar_compra'])->name('tienda.guardarcompra');

Route::post('/login', [usuariosController::class, 'login'])->name('login_usuarios');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/prueba-controller', [PruebaController::class, 'index']);

    Route::get('/logout', [usuariosController::class, 'logout'])->name('logout_usuarios');

    Route::get('/factura/{id_factura}/{id_comprobante}', [FacturasController::class, 'descargar_comprobante'])->name("factura.descargar_comprobante");
    Route::get('/generar-factura/{factura}', [FacturasController::class, 'generar_factura'])->name('factura.generar');
    Route::get('/autorizar-factura/{factura}', [FacturasController::class, 'autorizar_factura'])->name('factura.autorizar');
    Route::get('/ver-factura/{factura}', [FacturasController::class, 'visualizar_factura'])->name('factura.visualizar');
    
    Route::get('/facturas', [FacturasController::class, 'listado'])->name('facturas.listado');
    Route::post('/facturas/filtrado-listado', [FacturasController::class, 'filtrado_listado'])->name('facturas.filtrado_listado');

    Route::get('/facturas/editar/{factura}', [FacturasController::class, 'editar'])->name('facturas.editar');
    Route::put('/facturas/subir-pago/{factura}', [FacturasController::class, 'subir_comprobantes'])->name('facturas.subir_comprobantes');
    Route::put('/facturas/actualizar/{factura}', [FacturasController::class, 'actualizar'])->name('facturas.actualizar');
    Route::put('/cancelar-factura', [FacturasController::class, 'cancelar_factura'])->name('facturas.cancelar');
    Route::delete('/facturas/eliminar/{factura}', [FacturasController::class, 'eliminar'])->name('facturas.eliminar');

    /* Rutas para liberar */
    Route::get('/facturas/liberar-productos/{factura}/{ruc?}', [FacturasController::class, 'vista_liberar_producto'])->name('facturas.ver.liberar');
    Route::post('/liberar-producto/{factura}', [FacturasController::class, 'liberar_producto'])->name("facturas.liberar_producto");
    Route::post('/renovar-licencia-producto/{factura}', [FacturasController::class, 'renovar_licencia'])->name("facturas.renovar_licencia_producto");


    /* Rutas para admin firmas (Steban)*/
    Route::get('/revisor-firmas', [FirmaController::class, 'listado_revisor'])->name('firma.revisor');
    Route::get('/revisor-firmas-enviadas-correo', [FirmaController::class, 'listado_revisor_enviadas_correo'])->name('firma.revisor_correo');
    Route::get('/revisor-editar-firma/{firma}', [FirmaController::class, 'editar_revisor'])->name('firma.revisor_editar');

    /* Rutas para admin facturas (Joyce) */
    Route::get('/listado-facturados', [FacturasController::class, 'listado_revisor'])->name("facturas.revisor");
    Route::post('/facturas/filtrado-facturados', [FacturasController::class, 'filtrado_listado_revisor'])->name("facturas.filtrado_revisor");

    Route::get('/revisor-editar-factura/{factura}', [FacturasController::class, 'editar_revisor'])->name("facturas.revisor_editar");
    Route::get('/liberar-producto-manual/{factura}', [FacturasController::class, 'liberar_producto_manual'])->name("facturas.liberar_producto_manual");

    // Rutas para productos admin
    Route::prefix('productos')->group(function () {
        Route::get('/listado-admin', [ProductosController::class, 'listado'])->name('productos.listado');
        Route::post('/filtro-listado-admin', [ProductosController::class, 'listado_ajax'])->name('productos.listado.ajax');
        Route::get('/editar-admin/{producto}', [ProductosController::class, 'editar'])->name('productos.editar');
        Route::put('/actualizar/{producto}', [ProductosController::class, 'actualizar'])->name('productos.actualizar');
        Route::put('/aplicar-descuento-masivo', [ProductosController::class, 'actualizar_masivo'])->name('productos.actualizar.masivo');
        Route::put('/resetear-precios-defecto', [ProductosController::class, 'resetear_precios'])->name('productos.resetear_precios');
    });

    // Rutas para cupones 
    Route::prefix('cupones')->group(function () {
        Route::get('/listado', [CuponesController::class, 'listado'])->name('cupones.listado');
        Route::post('/filtrado-listado', [CuponesController::class, 'listado_ajax'])->name('cupones.listado.ajax');
        Route::get('/crear', [CuponesController::class, 'crear'])->name('cupones.crear');
        Route::post('/guardar', [CuponesController::class, 'guardar'])->name('cupones.guardar');
        Route::get('/editar/{cupon}', [CuponesController::class, 'editar'])->name('cupones.editar');
        Route::put('/actualizar/{cupon}', [CuponesController::class, 'actualizar'])->name('cupones.actualizar');
    });

    /* Rutas para cobros */
    Route::prefix('cobros')->group(function () {
        Route::get('/listado-vendedor', [CobrosController::class, 'listado_vendedor'])->name('cobros.listado.vendedor');
        Route::post('/filtrado-listado', [CobrosController::class, 'filtrado_listado_vendedor'])->name('cobros.listado.ajax');

        Route::get('/ver/{cobroid}/{id_comprobante}', [CobrosController::class, 'descargar_comprobante'])->name("cobros.descargar_comprobante");


        Route::get('/crear', [CobrosController::class, 'agregar'])->name('cobros.crear');
        Route::post('/guardar', [CobrosController::class, 'guardar'])->name('cobros.guardar');
        Route::get('/editar/{cobro}', [CobrosController::class, 'editar'])->name('cobros.editar');
        Route::put('/actualizar/{cobro}', [CobrosController::class, 'actualizar'])->name('cobros.actualizar');
        Route::delete('/eliminar/{cobro}', [CobrosController::class, 'eliminar'])->name('cobros.eliminar');


        // Rutas para revisor
        Route::get('/listado-revisor', [CobrosController::class, 'listado_revisor'])->name('cobros.listado.revisor');
        Route::get('/editar-revisor/{cobro}', [CobrosController::class, 'editar_revisor'])->name('cobros.editar_revisor');
        Route::post('/filtrado-listado-revisor', [CobrosController::class, 'filtrado_listado_revisor'])->name('cobros.listado.revisor_ajax');
        Route::put('/actualizar-revisor/{cobro}', [CobrosController::class, 'actualizar_revisor'])->name('cobros.actualizar_revisor');
    });

    /* Rutas para comisiones */
    Route::prefix('comisiones')->group(function () {
        Route::get('/listado', [ComisionesController::class, 'index'])->name('comisiones.listado');
        Route::post('/filtrado-listado', [ComisionesController::class, 'filtrado_listado_comisiones'])->name('comisiones.listado.ajax');

        Route::get('/listado-tecnicos', [ComisionesController::class, 'listado_tecnicos'])->name('comisiones.listado_tecnicos');
        Route::post('/filtrado-listado-tecnicos', [ComisionesController::class, 'filtrado_listado_comisiones_tecnicos'])->name('comisiones.listado_tecnicos.ajax');

        Route::get('/mis-comisiones', [ComisionesController::class, 'mis_comisiones_vendedor'])->name('comisiones.mi_listado');
        Route::post('/filtrado-mis-comisiones', [ComisionesController::class, 'filtrado_mis_comisiones_vendedor'])->name('comisiones.filtrado.miscomisiones');

        Route::put('/marcar-comision-pagado-vendedor', [ComisionesController::class, 'marcar_pagado_vendedores'])->name('comisiones.marcar_pagado.vendedor');
        Route::put('/marcar-comision-pagado-soportes', [ComisionesController::class, 'marcar_pagado_soportes'])->name('comisiones.marcar_pagado.soportes');

    });

    /* Rutas para cotizaciones */
    Route::controller(CotizacionController::class)->prefix('cotizacion')->group(function () {
        Route::get('/listado', 'index')->name('cotizacion.listado');
        Route::post('/guardar', 'registrar_cotizacion')->name('cotizacion.guardar');
    });

    /* Rutas para prospectos */
    Route::controller(ProspectoController::class)->prefix('prospecto')->group(function () {
        Route::get('/listado', 'listar')->name('prospecto.listado');
        Route::get('/comprobar/{identificacion}', 'comprobar')->name('prospecto.editar');
        Route::get('/crear', 'crear')->name('prospecto.vista_crear');
        Route::get('/editar/{prospecto}', 'editar')->name('prospecto.editar');
        Route::post('/guardar', 'guardar')->name('prospecto.guardar');
        Route::put('/actualizar/{prospecto}', 'actualizar')->name('prospecto.actualizar');
        Route::delete('/eliminar/{prospecto}', 'eliminar')->name('prospecto.eliminar');
    });


    /* Rutas anteriores */
    Route::get('/principal', function () {
        return view('auth.principal');
    });

    Route::get('/listado', [FirmaController::class, 'listado'])->name('firma.listado');
    Route::get('/clave', function () {
        return view('auth.cambiarclave');
    })->name('usuarios.clave');

    Route::post('/guardarclave', [usuariosController::class, 'clave'])->name('usuarios.guardarclave');
    Route::get('/editar/{firma}', [FirmaController::class, 'editar'])->name('firma.editar');
    Route::put('/actualizar/{firma}', [FirmaController::class, 'actualizar'])->name('firma.actualizar');

    Route::delete('/eliminar/{firma}', [FirmaController::class, 'eliminar'])->name('firma.eliminar');
    Route::get('/descarga/{firma}/{tipo}', [FirmaController::class, 'descarga'])->name('firma.descarga');
    Route::get('/visualizar-fotos/{firma}/{tipo}', [FirmaController::class, 'visualizar_imagen'])->name('firma.visualizar_imagen');
    Route::get('subir/uanataca/{solicitud}', [FirmaController::class, 'registrar_solicitud'])->name('firma.subirapi');
});
