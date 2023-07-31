<script>
    $(document).ready(function() {
        var table = $('#kt_datatable').DataTable({
            dom: "<'row'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6'l>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            responsive: true,
            processing: true,
            //Combo cantidad de registros a mostrar por pantalla
            lengthMenu: [
                [15, 25, 50, -1],
                [15, 25, 50, 'Todos']
            ],
            //Registros por pagina
            pageLength: 15,
            //Orden inicial
            order: [
                [0, 'asc']
            ],
            //Guardar pagina, busqueda, etc
            stateSave: true,
            //Trabajar del lado del server
            serverSide: true,
            //Peticion ajax que devuelve los registros
            ajax: {
                url: "{{ route($name) }}",
                type: 'GET'

            },
            columns: [{
                    data: 'ticketid',
                    name: 'ticketid',
                    searchable: false,
                    visible: true
                },
                {
                    data: 'numero_ticket',
                    name: 'numero_ticket',
                },
                {
                    data: 'ruc',
                    name: 'ruc',

                }, {
                    data: 'razon_social',
                    name: 'razon_social',
                },
                {
                    data: 'correo',
                    name: 'correo',
                },
                {
                    data: 'whatsapp',
                    name: 'whatsapp',
                },
                {
                    data: 'estado',
                    name: 'estado',
                },
                {
                    data: 'fecha_creado',
                    name: 'fecha_creado',
                    type: "date",
                },
                {
                    data: 'tiempo_activo',
                    name: 'tiempo_activo',
                    searchable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                },
            ],
            buttons: [{
                    extend: 'print',
                    title: 'Usuarios',
                    exportOptions: {
                        columns: ':not(.no-exportar)'
                    }
                },
                {
                    extend: 'copyHtml5',
                    title: 'Usuarios',
                    exportOptions: {
                        columns: ':not(.no-exportar)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    title: 'Usuarios',
                    exportOptions: {
                        columns: ':not(.no-exportar)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Usuarios',
                    exportOptions: {
                        columns: ':not(.no-exportar)'
                    }
                },
            ]
        });

        setInterval(() => {
            table.ajax.reload();
        }, (1000 * 30));

    });
</script>
