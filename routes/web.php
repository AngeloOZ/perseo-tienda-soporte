<?php

use App\Http\Controllers\adminController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\CiudadesController;
use App\Http\Controllers\CobrosClientesController;
use App\Http\Controllers\CobrosController;
use App\Http\Controllers\ComisionesController;
use App\Http\Controllers\CuponesController;
use App\Http\Controllers\FacturasController;
use App\Http\Controllers\FirmaController;
use App\Http\Controllers\LiberarLicenciasController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\SoporteEspcialController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\usuariosController;
use App\Http\Controllers\VerificarCobrosLotesController;
use App\Http\Controllers\WhatsappRenovacionesController;
use App\Models\SoporteEspecial;
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

Route::get('/referido/{id}', [usuariosController::class, 'validarVendedor'])->name('inicio');
Route::get('/facturacion/{id}', [usuariosController::class, 'datosFacturacion'])->name('inicio.facturacion');
Route::get('/estado-solicitud/{id}', [FirmaController::class, 'rastrearProceso'])->name('firma.estadosolicitud');
Route::get('/status/{cedula}', [FirmaController::class, 'validarProceso'])->name('validarestado');

Route::post('/ciudades', [CiudadesController::class, 'recuperarciudades'])->name('firma.recuperarciudades');
Route::post('/datos', [FirmaController::class, 'recuperardatos'])->name('firma.index');
Route::post('/email/datos', [adminController::class, 'verificarEmailCelular'])->name('admin.verificaremailcelular');
Route::post('/guardar', [FirmaController::class, 'guardar'])->name('firma.guardar');
Route::post('/reenviar/correo', [FirmaController::class, 'reenviar_correo'])->name('reenviar.correo');

Route::get('/{referido}/tienda', [TiendaController::class, 'listar_productos'])->name('tienda');
Route::get('/{referido}/tienda/resumen', [TiendaController::class, 'resumen_compra'])->name('tienda.checkout');
Route::get('/{referido}/tienda/finalizar-compra/{pago?}', [TiendaController::class, 'finalizar_compra'])->name('tienda.finalizar_compra');
Route::post('/registrar/compra',  [TiendaController::class, 'registar_compra'])->name('tienda.guardarcompra');

Route::get('/', [usuariosController::class, 'redirect_login']);
Route::get('/login', [usuariosController::class, 'vista_login'])->name('auth.login');
Route::post('/login', [usuariosController::class, 'login'])->name('login_usuarios');

Route::prefix('pagos')->group(function () {
    Route::get('/registrar-comprobante/{factura}', [CobrosClientesController::class, 'registrar_pago_cliente'])->name('pagos.registrar');
    Route::post('/guardar-comprobantes', [CobrosClientesController::class, 'guardar_pago'])->name('pagos.guardar');
    Route::post('/actualizar-comprobante', [CobrosClientesController::class, 'actualizar_pago'])->name('pagos.actualizar');

    Route::get('/reactivar-comprobante/{cobro}', [CobrosClientesController::class, 'reactivar_pago'])->name('pagos.reactivar');
});


Route::group(['middleware' => 'auth'], function () {
    Route::get('/facturas', function () {
        return redirect()->route('facturas.listado');
    });

    /* Rutas para seccion facturas */
    Route::prefix('factura')->group(function () {
        Route::get('/descargar/{id_factura}/{id_comprobante}', [FacturasController::class, 'descargar_comprobante'])->name("factura.descargar_comprobante");

        Route::get('/generar/{factura}', [FacturasController::class, 'generar_factura'])->name('factura.generar');
        Route::get('/autorizar/{factura}', [FacturasController::class, 'autorizar_factura'])->name('factura.autorizar');
        Route::get('/visualizar/{factura}', [FacturasController::class, 'visualizar_factura'])->name('factura.visualizar');
        Route::get('/listado', [FacturasController::class, 'listado'])->name('facturas.listado');
        Route::post('/filtrado-listado', [FacturasController::class, 'filtrado_listado'])->name('facturas.filtrado_listado');

        Route::get('/editar/{factura}', [FacturasController::class, 'editar'])->name('facturas.editar');
        Route::put('/actualizar/{factura}', [FacturasController::class, 'actualizar'])->name('facturas.actualizar');
        Route::put('/subir-pago/{factura}', [FacturasController::class, 'subir_comprobantes'])->name('facturas.subir_comprobantes');
        Route::put('/cancelar', [FacturasController::class, 'cancelar_factura'])->name('facturas.cancelar');
        Route::delete('/eliminar/{factura}', [FacturasController::class, 'eliminar'])->name('facturas.eliminar');

        Route::post('/registrar-capacitacion/{factura}', [SoporteEspcialController::class, 'registrar_capacitacion_ventas'])->name('soporte.registrar_capacitacion_ventas');
    });

    /* Rutas para liberar licencias */
    Route::prefix('factura/liberar')->group(function () {
        Route::get('producto/{factura}', [LiberarLicenciasController::class, 'redirigir_vista'])->name('liberarlicencias');
        // web
        Route::get('/web/{factura}/{ruc?}', [LiberarLicenciasController::class, 'vista_liberar_producto'])->name('facturas.ver.liberar');

        Route::post('/web/{factura}', [LiberarLicenciasController::class, 'liberar_producto'])->name("facturas.liberar_producto");

        Route::post('/renovar-web/{factura}', [LiberarLicenciasController::class, 'renovar_licencia'])->name("facturas.renovar_licencia_producto");

        Route::put('/reactivacion/{factura}', [LiberarLicenciasController::class, 'reactivar_liberacion'])->name('facturas.reactivar_liberacion');

        // contafacil
        Route::get('/contafacil/{factura}', [LiberarLicenciasController::class, 'vista_liberar_contafacil'])->name('liberar.vista.contafacil');
        Route::post('/contafacil/{factura}', [LiberarLicenciasController::class, 'liberar_licencia_contafacil'])->name('liberar.licencia.contafacil');
    });

    /* Rutas para admin firmas (Steban)*/
    Route::prefix('revisor-firmas')->group(function () {
        Route::get('/', [FirmaController::class, 'listado_revisor'])->name('firma.revisor');
        Route::post('/filtrado-listado', [FirmaController::class, 'filtrado_listado_revisor'])->name('firma.filtrado_revisor');

        Route::get('/enviadas-correo', [FirmaController::class, 'listado_revisor_enviadas_correo'])->name('firma.revisor_correo');
        Route::post('/filtrado-listado-enviadas-correo', [FirmaController::class, 'filtrado_listado_revisor_enviadas_correo'])->name('firma.filtrado_revisor_correo');

        Route::get('/editar-firma/{firma}', [FirmaController::class, 'editar_revisor'])->name('firma.revisor_editar');
    });

    /* Rutas para admin facturas (Joyce) */
    Route::prefix('revisor-facturas')->group(function () {
        Route::get('/', [FacturasController::class, 'listado_revisor'])->name('facturas.revisor');
        Route::get('/por-pagar', [FacturasController::class, 'listado_revisor_por_pagar'])->name('facturas.porpagar');
        Route::post('/filtrado-listado', [FacturasController::class, 'filtrado_listado_revisor'])->name('facturas.filtrado_revisor');

        Route::get('/editar-factura/{factura}', [FacturasController::class, 'editar_revisor'])->name('facturas.revisor_editar');
        Route::get('/liberar-producto-manual/{factura}', [FacturasController::class, 'liberar_producto_manual'])->name('facturas.liberar_producto_manual');

        Route::prefix('cobros-lotes')->group(function () {
            Route::get('listado', [VerificarCobrosLotesController::class, 'listar_cobros_lotes'])->name('pagos.lotes.list');
            Route::post('listado', [VerificarCobrosLotesController::class, 'procesar_cobro_lotes'])->name('pagos.lotes.post');
            Route::post('registro/lotes', [VerificarCobrosLotesController::class, 'registrar_cobro_sistema'])->name('cobros.registro.lotes');
            Route::get('descargar/plantilla', [VerificarCobrosLotesController::class, 'descargar_plantilla'])->name('cobros.descargar_plantilla');
        });

        Route::prefix('whatsapp')->group(function () {
            Route::get('/configuracion', [WhatsappRenovacionesController::class, 'index'])->name('facturas.whatsapp.config');
            Route::post('/iniciar', [WhatsappRenovacionesController::class, 'iniciar_whatsapp'])->name('facturas.whatsapp.iniciar');
            Route::post('/obtener-qr', [WhatsappRenovacionesController::class, 'obtener_qr_whatsapp'])->name('facturas.whatsapp.obtener.qr');
            Route::post('/cerrar', [WhatsappRenovacionesController::class, 'cerrar_whatsapp'])->name('facturas.whatsapp.cerrar');
            Route::post('/enviar-sms', [WhatsappRenovacionesController::class, 'enviar_sms_whatsapp'])->name('facturas.whatsapp.enviar.sms');
            Route::post('/borrar-token', [WhatsappRenovacionesController::class, 'eliminar_token'])->name('facturas.whatsapp.eliminar_token');
        });
    });

    /* Rutas para productos admin */
    Route::prefix('productos')->group(function () {
        Route::get('/listado-admin', [ProductosController::class, 'listado'])->name('productos.listado');
        Route::post('/filtro-listado-admin', [ProductosController::class, 'listado_ajax'])->name('productos.listado.ajax');
        Route::get('/editar-admin/{producto}', [ProductosController::class, 'editar'])->name('productos.editar');
        Route::put('/actualizar/{producto}', [ProductosController::class, 'actualizar'])->name('productos.actualizar');
        Route::put('/aplicar-descuento-masivo', [ProductosController::class, 'actualizar_masivo'])->name('productos.actualizar.masivo');
        Route::put('/resetear-precios-defecto', [ProductosController::class, 'resetear_precios'])->name('productos.resetear_precios');
    });

    /* Rutas para cupones */
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
        Route::post('/registrar-cobro-sistema', [CobrosController::class, 'registrar_cobro_sistema'])->name('cobros.registrar.sistema');
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

    /* Rutas para firmas */
    Route::prefix('firma')->group(function () {
        Route::get('/listado', [FirmaController::class, 'listado'])->name('firma.listado');
        Route::post('/filtrado-listado', [FirmaController::class, 'filtrado_listado'])->name('facturas.filtrado.listado');

        Route::get('/editar/{firma}', [FirmaController::class, 'editar'])->name('firma.editar');
        Route::put('/actualizar/{firma}', [FirmaController::class, 'actualizar'])->name('firma.actualizar');

        Route::delete('/eliminar/{firma}', [FirmaController::class, 'eliminar'])->name('firma.eliminar');

        Route::get('/descarga/{firma}/{tipo}', [FirmaController::class, 'descarga'])->name('firma.descarga');
        Route::get('/visualizar-fotos/{firma}/{tipo}', [FirmaController::class, 'visualizar_imagen'])->name('firma.visualizar_imagen');
        Route::get('subir/uanataca/{solicitud}', [FirmaController::class, 'registrar_solicitud'])->name('firma.subirapi');
    });

    /* Rutas para usuario */
    Route::prefix('usuario')->group(function () {
        Route::get('/logout', [usuariosController::class, 'logout'])->name('logout_usuarios');
        Route::get('/clave', [usuariosController::class, 'cambiar_clave'])->name('usuarios.clave');
        Route::post('/guardarclave', [usuariosController::class, 'clave'])->name('usuarios.guardarclave');
    });

    /* Rutas para demos y lite */
    Route::prefix('demos')->group(function () {

        Route::get('/listado', [SoporteEspcialController::class, 'listado_demos_lites'])->name('demos.listado');
        Route::post('/filtrado-listado', [SoporteEspcialController::class, 'filtrado_listado_demos_lites'])->name('demos.filtrado.listado');

        Route::get('/crear', [SoporteEspcialController::class, 'crear_demo_lite'])->name('demos.crear');
        Route::post('/guardar', [SoporteEspcialController::class, 'guardar_demo_lite'])->name('demos.guardar');

        Route::get('/ver/{soporte}', [SoporteEspcialController::class, 'ver_demo_lite'])->name('demos.ver');
        Route::get('convertir/lite/{soporte}', [SoporteEspcialController::class, 'convertir_lite'])->name('demos.convertir.lite');

        Route::post('/liberar-lite/{soporte}', [SoporteEspcialController::class, 'liberar_lite'])->name('demos.liberar.lite');
    });

    Route::prefix('bitrix')->group(function () {

        Route::get('/list', [BitrixController::class, 'index'])->name('bitrix.list');

        Route::post('/promedio-ventas', [BitrixController::class, 'obtener_promedio_ventas'])->name('bitrix.promedio_ventas');
        Route::post('/tasa-utilidad-prospectos', [BitrixController::class, 'obtener_tasa_utilidad_prospectos'])->name('bitrix.tasa_utilidad_prospectos');
        Route::post('/tiempo-de-conversion', [BitrixController::class, 'obtener_tiempo_de_cierre_de_conversion'])->name('bitrix.tiempo_de_conversion');

        Route::post('/tasa-conversion-prospectos', [BitrixController::class, 'obtener_tasa_de_conversion'])->name('bitrix.tasa_conversion_prospectos');
    });
});
