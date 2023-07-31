@extends('auth.layouts.app')

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
                                    <h3 class="card-label">Listado de cupones</h3>
                                </div>


                                <div class="card-toolbar">
                                    <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                        <div class="btn-group" role="group" aria-label="First group">

                                            <a href="{{ route('cupones.crear') }}" class="btn btn-primary btn-icon"
                                                data-toggle="tooltip" title="Agregar cupón">
                                                <i class="la la-plus"></i>
                                            </a>

                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="form-group" style="width: 200px;">
                                        <label for="">Estado de cupones</label>
                                        <select class="form-control" id="estadoCupon">
                                            <option value="">Todos</option>
                                            <option value="1" selected>Activos</option>
                                            <option value="0">Inactivos</option>
                                        </select>
                                    </div>
                                </div>
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Cupón</th>
                                            <th>Promocion</th>
                                            <th>Fecha de expiración</th>
                                            <th>Veces usado</th>
                                            <th>estado</th>
                                            <th>tipo</th>
                                            <th>Acciones</th>
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

@section('modal')
    <div id="copyCupon" class="modal fade">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">Enlace con Cupón</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label>Enlace</label>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly id="enlaceCupon">
                            <div class="input-group-append">
                                <button class="btn btn-primary" id="btnCopy" type="button">Copiar</button>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cerrar</button>
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
                ajax: {
                    url: "{{ route('cupones.listado.ajax') }}",
                    type: 'POST',
                    data: function(d) {
                        //Valores de filtro a enviar a la ruta clientes.index
                        d.estado = $("#estadoCupon").val();
                    }

                },
                columns: [{
                        data: 'cuponid',
                        name: 'cuponid',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'cupon',
                        name: 'cupon',
                    },
                    {
                        data: 'descuento',
                        name: 'descuento',
                    },
                    {
                        data: 'tiempo_vigencia',
                        name: 'tiempo_vigencia',
                    },
                    {
                        data: 'veces_usado',
                        name: 'veces_usado',
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                    },
                    {
                        data: 'tipo',
                        name: 'tipo',
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

            $('#estadoCupon').on('change', function(e) {
                e.preventDefault();
                table.draw();
            });

        });

        $(document).on('click', '.copyCupon', function(e) {
            e.preventDefault();
            const url = $(this).attr('data-url-cupon');
            $("#enlaceCupon").val(url);
            $("#copyCupon").modal("show");
        });

        $(document).on('click', '#btnCopy', function(e) {
            e.preventDefault();
            this.setAttribute('disabled', 'disabled');
            const copyText = document.getElementById("enlaceCupon");
            copyText.select();
            document.execCommand("copy");
            this.textContent = 'Copiado';
            setTimeout(() => {
                this.textContent = 'Copiar';
                this.removeAttribute('disabled');
            }, 600);
        });

        function validateDescuento() {
            let descuento = parseFloat($("#descuento").val());
            if (descuento > 100 || descuento < 0) {
                alert("El descuento debe estar entre 0% y 100%");
                $("#descuento").val(0);
                return
            }
        }

    </script>
@endsection
