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
                                    <h3 class="card-label">Listado de productos</h3>
                                </div>


                                <div class="card-toolbar">
                                    <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="">
                                        <div class="btn-group" role="group" aria-label="First group">

                                            <button class="btn btn-primary btn-icon resetear-modal" id="buttonReset"
                                                data-toggle="tooltip" title="Resetear precios"><i
                                                    class="la la-undo-alt"></i>
                                            </button>

                                            <button class="btn btn-success btn-icon modal-implementacion" id="buttonSave"
                                                data-toggle="tooltip" title="Descuento por categoria"><i
                                                    class="la la-donate"></i>
                                            </button>

                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="form-group" style="width: 200px;">
                                        <label for="">Distribuidores</label>
                                        <select class="form-control" id="distribuidores">
                                            <option value="">Todos</option>
                                            <option value="1">Perseo Alfa</option>
                                            <option value="2">Perseo Matriz</option>
                                            <option value="3">Perseo Delta</option>
                                            <option value="4">Perseo Omega</option>
                                        </select>
                                    </div>
                                </div>
                                <table class="table table-sm table-bordered table-head-custom table-hover text-center"
                                    id="kt_datatable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Distribuidor</th>
                                            <th>Producto</th>
                                            <th>Categoria</th>
                                            <th>Precio sin IVA</th>
                                            <th>Precio con IVA</th>
                                            <th>Descuento</th>
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
    @include('auth.productos.inc.categoria_modal')
    @include('auth.productos.inc.resetear_modal')
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
                    url: "{{ route('productos.listado.ajax') }}",
                    type: 'POST',
                    data: function(d) {
                        //Valores de filtro a enviar a la ruta clientes.index
                        d.distribuidor = $("#distribuidores").val();
                    }

                },
                columns: [{
                        data: 'productos_homologados_id',
                        name: 'productos_homologados_id',
                        searchable: false,
                        visible: false
                    },
                    {
                        data: 'distribuidor',
                        name: 'distribuidor',
                    },
                    {
                        data: 'nombre',
                        name: 'nombre',
                    },
                    {
                        data: 'categoria',
                        name: 'categoria',
                    },
                    {
                        data: 'precio',
                        name: 'precio',
                    },
                    {
                        data: 'precioiva',
                        name: 'precioiva',
                    },
                    {
                        data: 'descuento',
                        name: 'descuento',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: "text-center"
                    },
                ],
                columnDefs: [{
                    targets: -5,
                    title: 'Categoria',
                    render: function(data, type, full, meta) {
                        let categoria = "FACTURITO";
                        switch (data) {
                            case 2:
                                categoria = "FIRMA ELECTRONICA";
                                break;
                            case 3:
                                categoria = "PERSEO PC";
                                break;
                            case 4:
                                categoria = "CONTAFACIL";
                                break;
                            case 5:
                                categoria = "PERSEO WEB";
                                break;
                            case 6:
                                categoria = "WHAPI";
                                break;
                        }
                        return categoria;
                    },
                }, ],
            });

            $('#kt_search').on('click', function(e) {
                e.preventDefault();
                table.draw();
            });

            $('#distribuidores').on('change', function(e) {
                e.preventDefault();
                table.draw();
            });

        });

        $(document).on('click', '.resetear-modal', function(e) {
            e.preventDefault();
            $("#resetear-modal").modal("show");
        });

        $(document).on('click', '.modal-implementacion', function(e) {
            e.preventDefault();
            $("#implementacion-modal").modal("show");
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
