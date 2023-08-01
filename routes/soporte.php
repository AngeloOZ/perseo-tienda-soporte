<?php

use App\Http\Controllers\SoporteController;
use App\Http\Controllers\SoporteEspcialController;
use App\Http\Controllers\TicketSoporteController;
use App\Http\Controllers\WhatsappController;
use App\Models\SoporteEspecial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('soporte')->group(function () {

    /* Tickets clientes */
    Route::get('/crear-ticket/{producto?}/{distribuidorid?}', [TicketSoporteController::class, 'index'])->name("soporte.crear.ticket");
    Route::get('/consultar-estado/{ruc}', [TicketSoporteController::class, 'validar_ticket_activo'])->name("soporte.consultar_estado");
    Route::get('/resultado-registro-ticket/{numero?}', [TicketSoporteController::class, 'resultado_registro'])->name("soporte.resultado_registro");
    Route::post('/crear-ticket', [TicketSoporteController::class, 'crear_ticket'])->name("soporte.crear_ticket");

    Route::get('/calificar-soporte/{ticket}', [TicketSoporteController::class, 'calificar_soporte_vista'])->name('soporte.calificar_ticket');

    Route::post('/calificar-soporte', [TicketSoporteController::class, 'registrar_calificacion_soporte'])->name('soporte.registrar_califcacion');

    /* Login */
    Route::get('/login',  [SoporteController::class, 'render_login'])->name('soporte.auth.login');
    Route::post('/login', [SoporteController::class, 'login_soporte'])->name('soporte.login');


    Route::middleware(['auth'])->group(function () {

        Route::get('redirect-index-by-rol', [SoporteController::class, 'redirect_by_rol'])->name("redirect.rol");

        Route::post('enviar-correo-cliente', [TicketSoporteController::class, 'enviar_correo_cliente'])->name('soporte.enviar_correo_cliente');
        Route::get('/editar-ticket/{ticket}', [TicketSoporteController::class, 'editar_ticket'])->name('soporte.editar');
        Route::put('/actualizar-ticket/{ticket}', [TicketSoporteController::class, 'actualizar_estado_ticket'])->name('soporte.actualizar_estado_ticket');


        Route::prefix('tecnico')->group(function () {
            Route::get('/listado-tickets-activos', [TicketSoporteController::class, 'listado_de_tickets_activos'])->name("soporte.listado.activos");

            Route::get('/listado-tickets-desarrollo', [TicketSoporteController::class, 'listado_de_tickets_desarrollo'])->name("soporte.listado.desarrollo");
            Route::post('/filtrado/listado-desarollo', [TicketSoporteController::class, 'filtrado_listado_de_tickets_desarrollo'])->name("soporte.filtrado.listado_desarrollo");

            Route::get('/listado-tickets-cerrados', [TicketSoporteController::class, 'listado_de_tickets_cerrados'])->name("soporte.listado.cerrados");
            Route::get('/cambiar-estado-disponibilidad/{id_user?}', [TicketSoporteController::class, 'cambiar_disponibilidad'])->name('soporte.cambiar.disponibilidad');

            Route::get('/mis-calificaciones', [TicketSoporteController::class, 'ver_resporte_calificacione_tecnico'])->name("soporte.mis_calificaciones");
            Route::post('/filtrado-mis-calificaciones', [TicketSoporteController::class, 'filtrado_reporte_calificaciones_tecnico'])->name("soporte.mis_calificaciones_filtrado");

            Route::get('/ver-calificaciones-tecnicos', [TicketSoporteController::class, 'ver_calificaciones_tecnicos'])->name('soporte.ver.calificaciones.tecnicos');
            Route::get('/reactivar-encuesta/{ticket}', [TicketSoporteController::class, 'reactivar_ecuesta'])->name('soporte.reactivar_ecuesta');

            Route::get('/listado-soportes-especiales', [SoporteEspcialController::class, 'listar_soporte_especial'])->name('sop.listar_soporte_especial');
            Route::post('/filtrado-soportes-especiales', [SoporteEspcialController::class, 'filtrado_soporte_especial_tecnico'])->name('soporte.filtrado.tec');
            Route::get('/agregar-soportes-especiales', [SoporteEspcialController::class, 'agregar_soporte_especial'])->name('sop.agregar_soporte_especial');
            Route::get('/editar-soportes-especiales/{soporte}', [SoporteEspcialController::class, 'editar_soporte_especial'])->name('sop.editar_soporte_especial');
            Route::post('/registrar-soportes-especiales', [SoporteEspcialController::class, 'registrar_soporte_especial'])->name('sop.registrar_soporte_especial');
            Route::put('/actualizar-soportes-especiales/{soporte}', [SoporteEspcialController::class, 'actualizar_soporte_especial'])->name('sop.actualizar_soporte_especial');
            Route::post('/registrar-actividad/{soporte}', [SoporteEspcialController::class, 'registrar_actividad_soporte'])->name('sop.registrar_actividad_soporte');
        });


        Route::prefix('desarrollo')->group(function () {
            Route::get('/listado-tickets-revisor', [TicketSoporteController::class, 'listado_de_tickets_desarrollo_revisor'])->name("soporte.listado.revidor.desarrollo");

            Route::get('/editar-ticket/{ticket}', [TicketSoporteController::class, 'editar_ticket_desarrollo'])->name('soporte.editar.desarrollo');
        });

        Route::prefix('revisor')->group(function () {
            Route::get('/listado-tickets-revisor', [TicketSoporteController::class, 'listado_tickets_revisor'])->name('soporte.listado.revisor');
            Route::post('/listado-tickets-revisor', [TicketSoporteController::class, 'filtrado_tickets_revisor'])->name('soporte.listado_filtrado.revisor');
            Route::get('/editar-ticket-revisor/{ticket}', [TicketSoporteController::class, 'editar_ticket_revisor'])->name('soporte.editar.revisor');
            Route::get('/estado-de-tecnicos', [TicketSoporteController::class, 'listado_estado_tecnicos'])->name('soporte.listado.estado_tecnicos');
            Route::delete('/eliminar-soporte-revisor/{ticket}', [TicketSoporteController::class, 'eliminar_soporte_revisor'])->name('soporte.eliminar_ticket');

            Route::get('/reporte-tickets-tecnicos', [TicketSoporteController::class, 'ver_resporte_soportes'])->name('soporte.reporte_soporte');
            Route::post('/reporte-tickets-tecnicos', [TicketSoporteController::class, 'filtrado_reporte_soporte'])->name('soporte.filtrado_reporte_soporte');

            Route::get('/reporte-calificaciones-tecnicos', [TicketSoporteController::class, 'ver_resporte_calificaciones'])->name('soporte.reporte_calificaicones');
            Route::post('/reporte-calificaciones-tecnicos', [TicketSoporteController::class, 'filtrado_reporte_calificaciones'])->name('soporte.filtrado_reporte_calificaicones');

            Route::get('/listado-revisor-soportes-especial', [SoporteEspcialController::class, 'revisor_listar_soporte_especial'])->name('soporte.revisor_listar_soporte_especial');
            Route::post('/filtrado-revisor-soportes-especial', [SoporteEspcialController::class, 'filtrado_soporte_especial'])->name('soporte.filtrado_soporte_especial');
        });

        Route::prefix('calificaciones')->group(function () {
            Route::get('/listado-calificaciones', [TicketSoporteController::class, 'listado_calificaciones'])->name('calificaciones.listado');
            Route::post('/filtro-listado-calificaciones', [TicketSoporteController::class, 'filtro_listado_calificaciones'])->name('calificaciones.filtro.listado');

            Route::get('/listado-justificadas', [TicketSoporteController::class, 'listado_justificadas'])->name('calificaciones.justificadas');
            Route::post('/filtro-listado-justificadas', [TicketSoporteController::class, 'filtro_listado_justificadas'])->name('calificaciones.filtro.justificadas');

            Route::put('/actualizar-estado/{encuesta}', [TicketSoporteController::class, 'actualizar_estado_encuesta'])->name('calificaciones.actualizar.estado');
            Route::put('/registrar-justificacion/{encuesta}', [TicketSoporteController::class, 'registrar_justificacion'])->name('calificaciones.registrar.justificacion');
        });

        Route::prefix('whatsapp')->group(function () {
            Route::get('/configuracion', [WhatsappController::class, 'index'])->name('config.whatsapp');
            Route::post('/iniciar', [WhatsappController::class, 'iniciar_whatsapp'])->name('whatsapp.iniciar');
            Route::post('/obtener-qr', [WhatsappController::class, 'obtener_qr_whatsapp'])->name('whatsapp.obtener.qr');
            Route::post('/cerrar', [WhatsappController::class, 'cerrar_whatsapp'])->name('whatsapp.cerrar');
            Route::post('/enviar-sms', [WhatsappController::class, 'enviar_sms_whatsapp'])->name('whatsapp.enviar.sms');
            Route::post('/borrar-token', [WhatsappController::class, 'eliminar_token'])->name('whatsapp.eliminar_token');
        });

        Route::prefix('soporte-especial')->group(function () {
            /* tienda */
            Route::post('/registrar-capacitacion-ventas/{factura}', [SoporteEspcialController::class, 'registrar_capacitacion_ventas'])->name('soporte.registrar_capacitacion_ventas');


            /* supervisor */
            Route::get('/listado', [SoporteEspcialController::class, 'supervisor_listar_soporte'])->name('especiales.listado_supervisor');
            Route::post('/filtrado-supervisor', [SoporteEspcialController::class, 'filtrado_supervisor_soporte'])->name('especiales.filtrado_supervidor');
        });
    });
});
