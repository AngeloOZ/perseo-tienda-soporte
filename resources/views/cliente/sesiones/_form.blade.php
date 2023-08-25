@csrf
<ul class="nav nav-tabs nav-tabs-line nav-bold">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#datossesion">Datos Planificación</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#temassesion">Planificación</a>
    </li>
</ul>
<div class="tab-content mt-5" id="myTabContent">
    <div class="tab-pane fade show active" id="datossesion" role="tabpanel">

        <div class="container-fluid">
            <div class="form-group row">
                <input type="hidden" id="tsesion" name="tsesion" />

                <div class="col-lg-6">
                    <label for="">Planificaciones</label>
                    <input type="text" class="form-control" id="planificacionesid" name="planificacionesid"
                        value="{{ $planificaciones->descripcion }}" disabled />
                </div>
                <div class="col-lg-6">
                    <label for="">Producto</label>
                    <input type="text" class="form-control" id="productosid" name="productosid"
                        value="{{ $productos->descripcion }}" disabled />
                </div>

            </div>

            <div class="form-group row">

                <div class="col-lg-6">
                    <label>Técnicos:</label>
                    <input type="text" class="form-control" id="tecnicosid" name="tecnicosid"
                        value="{{ $tecnicos->nombres }}" disabled />

                </div>
                <div class="col-lg-6">
                    <label for="">Enlace</label>
                    <input type="text" class="form-control" name="enlace" id="enlace" autocomplete="off"
                        disabled="disabled" value="{{ $sesiones->enlace }}">

                </div>

            </div>

            <div class="form-group row">

                <div class="col-lg-6">
                    <label for="">Aceptado por el cliente</label>
                    <span class="switch switch-outline switch-icon switch-primary">
                        <label>
                            <input type="checkbox" name="revisado" id="revisado" disabled
                                @if ($planificaciones->revisioncliente == 1) checked @endif />
                            <span></span>
                        </label>
                    </span>

                </div>

            </div>


            <div class="row">
                <div class="col-12 col-md-6 col-lg-5 col-xl-6">
                    <label for="">Descripción a realizar:</label>
                    <div class="d-flex">
                        <textarea class="form-control" name="descripcion" id="descripcion" cols="40%" rows="5" disabled> {{ $sesiones->descripcion }}</textarea>
                    </div>

                </div>

                <div
                    class="d-flex col-6 col-md-3 col-lg-4 col-xl-4 mt-3 md-mt-0 align-items-center justify-content-center ">

                    <label style="font-weight: bold; ">Tiempo Ocupado: </label>
                    <span class="ml-2 mb-md-2 "
                        id="tiempoOcupado-{{ $sesiones->sesionesid }}">{{ $sesiones->suma }}</span>

                </div>

            </div>

        </div>

    </div>
    <div class="tab-pane fade" role="tabpanel" id="temassesion">
        <table class="table table-sm table-bordered table-head-custom table-hover" id="temas">
            <thead>
                <tr>
                    <th>Categorias</th>
                    <th>Temas</th>
                    <th>Identificador</th>
                    <th>Calificación</th>
                    <th>Youtube</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


@section('script')
    <script>
        $(document).ready(function() {

            const rutaTemas = `{{ route('sesiones.verificar', ['sesiones' => 'valor']) }}"`;
            const url = rutaTemas.replace('valor', `{{ $sesiones->sesionesid }}`);

            if ($.fn.DataTable.isDataTable('#temas')) {
                $('#temas').DataTable().destroy();
            }

            var table1 = $('#temas').DataTable({
                responsive: true,
                processing: true,
                bFilter: false,
                serverSide: true,
                paging: false,
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
                        var valorCat = api.cell(i, 2).data();
                        if (last !== group) {
                            $(rows).eq(i).before(
                                '<tr class="group" ><td colspan="3" class="nombreCategoria" id="nombreCategoria-' +
                                valorCat + '">' + group +
                                '</td></tr>',
                            );
                            last = group;
                        }
                    });
                },
                columns: [{
                        data: 'categorias',
                        name: 'categorias.descripcion',
                        orderable: false,
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'temas',
                        name: 'temas.descripcion',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'identificador',
                        name: 'categorias.categoriasid',
                        orderable: false,
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'calificacion',
                        name: 'calificacion',
                        orderable: false,
                        searchable: false,
                        visible: true,
                    },
                    {
                        data: 'youtube',
                        name: 'youtube',
                        orderable: false,
                        searchable: false,
                        visible: true,
                    }
                ],
            });

            $(document).on('change', '.radio-calificacion', function() {
                const idRadioSeleccionado = $(this).attr('id');
                const idRadioValue = $(this).attr('value');
                var idRadioTema = idRadioSeleccionado.split('-')[1];
                var planificacionCliente = '{{ $sesiones->planificacionesid }}';

                $.ajax({
                    url: '{{ route('sesiones.guardarcalificacion') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        temas: idRadioTema,
                        planificaciones: planificacionCliente,
                        calificacion: idRadioValue

                    },
                    success: function(datos) {
                        console.log(datos)

                    }
                })

                /* const categoriaCheck = document.getElementById(idCheckboxSeleccionado);
                const checkboxesAsignados = document.querySelectorAll('.' + idCheckboxSeleccionado);
                let opcion = false;
                if (checkboxesAsignados) {
                    if (categoriaCheck.checked) {
                        opcion = true
                    } else {
                        opcion = false
                    }
                    checkboxesAsignados.forEach(asignado => {
                        asignado.checked = opcion;
                    });
                } */
            });
            /* cambiarPlanificaciones();


             */
        })

        $(document).on('click', '.nombreCategoria', function() {
            const idnombreCategoria = $(this).attr('id');
            var numero = idnombreCategoria.split('-')[1];
            const filas = $('.checkCat-' + numero);
            filas.each(function() {
                var fila = $(this).closest('tr');
                fila.is(':hidden') ? fila.show() : fila.hide();

            });
        });

        function cambiarPlanificaciones() {
            var seleccionado = '';
            let clientes = document.getElementById('clientesid').value;

            if (clientes > 0) {
                $.ajax({
                    url: '{{ route('sesiones.recuperarplanificaciones') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        clientesid: clientes

                    },
                    success: function(datos) {

                        if (datos) {
                            $('#planificacionesid').empty();
                            datos.map(function(data) {
                                if (data.planificacionesid == '{{ $sesiones->planificacionesid }}') {
                                    seleccionado = 'selected';

                                } else {
                                    seleccionado = 'noselected';
                                }

                                $('#planificacionesid').append('<option class="' + data
                                    .producto + '-' + data.productosid + '" value="' + data
                                    .planificacionesid +
                                    '" ' + seleccionado + ' >' + data
                                    .descripcion + '</option>')
                            });
                            $('#planificacionesid').trigger('change');
                        }
                    }
                })
            }
        }
    </script>
@endsection
