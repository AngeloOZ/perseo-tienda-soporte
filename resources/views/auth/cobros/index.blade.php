@extends('auth.layouts.app')
@section('title_page', 'Listado de técnicos')
@section('contenido')
    <style>
        #kt_datatable td {
            padding: 3px;
        }
    </style>
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <!--begin::Card-->
                        <div class="card card-custom card-sticky" id="kt_page_sticky_card">
                            <div class="card-header ">
                                <div class="card-title">
                                    <h3 class="card-label"> Listado de cobros <small>Vendedor</small></h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-5">
                                    <div class="form-group">
                                        <label>Estado:</label>
                                        <select class="form-control datatable-input" id="filtroEstado">
                                            <option value="" selected>Todos</option>
                                            <option value="1">Registrados</option>
                                            <option value="2">Verificados</option>
                                            <option value="3">Rechazados</option>
                                        </select>
                                    </div>
                                    <a href="{{ route('cobros.crear') }}"
                                        class="btn btn-success align-self-center">Registrar cobro</a>
                                </div>
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th class="no-exportar">#</th>
                                            <th>Nº Factura</th>
                                            <th>Obs. Revisor</th>
                                            <th>Fecha de registro</th>
                                            <th>Estado</th>
                                            <th class="no-exportar">Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
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
                    [0, 'desc']
                ],
                //Guardar pagina, busqueda, etc
                stateSave: true,
                //Trabajar del lado del server
                serverSide: true,
                //Peticion ajax que devuelve los registros
                searchDelay: 500,
                deferRender: true,
                paging: true,
                ajax: {
                    url: "{{ route('cobros.listado.ajax') }}",
                    type: 'POST',
                    data: function(d) {
                        //Valores de filtro a enviar a la ruta clientes.index
                        d.estado = $("#filtroEstado").val();
                    }
                },
                columns: [{
                        data: 'cobrosid',
                        name: 'cobrosid',
                        searchable: false,
                        visible: true
                    },
                    {
                        data: 'secuencias',
                        name: 'secuencias',

                    }, {
                        data: 'obs_revisor',
                        name: 'obs_revisor',
                    },
                    {
                        data: 'fecha_registro',
                        name: 'fecha_registro',
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: "text-center"
                    },
                ],
            });

            //Clic en boton buscar
            $('#filtroEstado').on('change', function(e) {
                e.preventDefault();
                table.draw();
            });

        });
    </script>
@endsection
