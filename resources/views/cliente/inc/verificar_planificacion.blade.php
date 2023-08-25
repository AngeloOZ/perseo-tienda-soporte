<div id="sesion-modal" class="modal fade">
    <div>
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <input type="hidden" id="sesion-verificar">
                <div class="modal-header">
                    <h4 class="modal-title h6">Planificación</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>

                <div class="modal-body ">
                    <table class="table table-sm table-bordered table-head-custom table-hover"
                        id="temasplanificaciones">
                        <thead>
                            <tr>
                                <th>Categorias</th>
                                <th>Temas</th>
                            </tr>
                        </thead>
                    </table>

                    <div class="text-center">
                        <button type="button" class="btn btn-link mt-2 cancelarO"
                            data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary mt-2 guardarO"
                            onclick="guardarRevision()">Aceptar</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@section('scriptP')
    <script>
        function temas() {
            const rutaTemas = `{{ route('sesiones.verificar', ['sesiones' => 'valor']) }}"`;
            const url = rutaTemas.replace('valor', $("#sesion-verificar").val());
            if ($.fn.DataTable.isDataTable('#temasplanificaciones')) {
                $('#temasplanificaciones').DataTable().destroy();
            }
            var table1 = $('#temasplanificaciones').DataTable({
                responsive: true,
                processing: true,
                bFilter: false,
                serverSide: true,
                paging: false,
                bPaginate: false,
                bInfo: false,
                //Peticion ajax que devuelve los registros
                ajax: {
                    url: url,
                    type: 'GET'
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    var rows = api.rows({
                        page: 'current'
                    }).nodes();
                    var last = null;

                    api.column(0, {
                        page: 'current'
                    }).data().each(function(group, i) {

                        if (last !== group) {
                            $(rows).eq(i).before(
                                '<tr class="group" ><td colspan="2" class="nombreCategoria">' +
                                group +
                                '</td></tr>',
                            );
                            last = group;
                        }
                    });
                },
                columns: [{
                        data: 'categorias',
                        name: 'categorias',
                        orderable: false,
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'temas',
                        name: 'temas',
                        orderable: false,
                        searchable: false,
                    }

                ]
            });
        }

        function guardarRevision() {
            const sesiones = $('#sesion-verificar').val();
            const rutaVer = `{{ route('sesiones.ver', ['sesiones' => 'valor']) }}`;
            const url = rutaVer.replace('valor', sesiones);

            $.post('{{ route('sesiones.ingresarRevision') }}', {
                _token: '{{ csrf_token() }}',
                sesionesid: sesiones,
            }, function(data) {
                if (data == 1) {
                    location.href = url;
                } else {
                    location.href = url;

                }
            })

        }
    </script>
@endsection
