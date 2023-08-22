@extends('soporte.auth.layouts.app')
@section('title_page', 'Listado de asignaciones')
@section('contenido')
    <style>
        #asignacion td {
            padding: 3px;
        }
    </style>
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-custom" id="kt_page_sticky_card">
                            <div class="card-header ">
                                <div class="card-title">
                                    <h3 class="card-label">Producto Asignación</h3>
                                </div>
                                <form method="POST" action="{{ route('asignacion.actualizar', $producto) }}"
                                    id="formulario">
                                    @method('PUT')
                                    @csrf
                                    <div class="card-toolbar">
                                        <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                            <div class="btn-group" role="group" aria-label="First group">

                                                <a href="{{ route('asignacion.index') }}" class="btn btn-secondary btn-icon"
                                                    data-toggle="tooltip" title="Volver"><i
                                                        class="la la-long-arrow-left"></i></a>

                                                <button type="submit" class="btn btn-success btn-icon"
                                                    data-toggle="tooltip" title="Guardar"><i
                                                        class="la la-save"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" id="producto" name="producto" value="{{ $producto }}">
                                    <input type="hidden" id="asignadosid" name="asignadosid"
                                        value="{{ $asignados->asignadosid }}">
                                </form>
                            </div>


                            <div class="card-body">
                                <!--begin: Datatable-->

                                <table class="table table-sm table-bordered table-head-custom table-hover " id="asignacion">
                                    <thead>
                                        <tr>
                                            <th>Categorias</th>
                                            <th>Temas</th>
                                            <th>accion</th>
                                        </tr>
                                    </thead>
                                </table>

                                <!--end: Datatable-->
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modal')
    @include('modals.delete_modal')
@endsection
@section('script')
    <script>
        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled', true);
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {

            //inicializar datatable
            var table1 = $('#asignacion').DataTable({

                responsive: true,
                processing: true,
                bFilter: false,
                serverSide: true,
                paging: false,
                //Peticion ajax que devuelve los registros
                ajax: {
                    url: "{{ route('asignacion', '$producto') }}",
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
                                '<tr class="group"><td colspan="3">' + group + '</td></tr>',
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
                        data: 'accion',
                        name: 'accion',
                        orderable: false,
                        searchable: false,
                    }
                ],
                initComplete: function(settings, json) {
                    //Al terminar de llenar tabla, cargar permisos
                    var asignadosid = $("#asignadosid").val();
                    var array = asignadosid.split(';');


                    for (var i = 0; i < array.length; i++) {
                        if (array[i] != "") {
                            $('#' + array[i]).prop('checked', true);

                        }
                    }
                }
            });

            $('#formulario').submit(function(event) {
                event.preventDefault();
                asignadosid = '';

                $("#asignacion tbody td input").each(function() {
                    if ($(this).prop('checked')) {
                        asignadosid = asignadosid + $(this).attr('id') + ';';

                    }


                });
                $('#asignadosid').val(asignadosid)
                $(this).unbind('submit').submit();
            });

        });
    </script>
@endsection
