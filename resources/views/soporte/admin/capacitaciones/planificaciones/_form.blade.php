@php
    use App\Constants\ConstantesTecnicos;
    $listadoClientes = App\Models\Clientes::select('clientesid', 'razonsocial')
        ->where('distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid)
        ->where('estado', 1)
        ->get();
    
    $listadoTecnicos = App\Models\Tecnicos::select('tecnicosid', 'nombres')
        ->where('rol', ConstantesTecnicos::ROL_TECNICOS)
        ->where('distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid)
        ->where('estado', 1)
        ->get();
    
    $listadoProductos = App\Models\Productos2::select('productosid', 'descripcion')->get();
    
    $temasCheck = App\Models\PlanificacionesDetalles::select('temasid')
        ->where('planificacionesid', $planificaciones->planificacionesid)
        ->get();

    $disabled = Auth::guard('tecnico')->user()->rol == ConstantesTecnicos::ROL_TECNICOS ? 'disabled' : '';

@endphp
@csrf
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<style>
    #temas td {
        padding: 3px;
    }

    .tagify--outside {
        border: 0;
        width: 100%;
    }

    .tagify--outside .tagify__input {
        order: -1;
        flex: 100%;
        border: 1px solid var(--tags-border-color);
        margin-bottom: 1em;
        transition: .1s;
    }

    .tagify--outside .tagify__input:hover {
        border-color: var(--tags-hover-border-color);
    }

    .tagify--outside.tagify--focus .tagify__input {
        transition: 0s;
        border-color: var(--tags-focus-border-color);
        /* border-color: transparent; */
    }
</style>
<ul class="nav nav-tabs nav-tabs-line nav-bold">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#datosplanificacion">Datos Planificación</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#temasplanificacion">Planificación</a>
    </li>
</ul>

<div class="tab-content mt-5" id="myTabContent">
    <div class="tab-pane fade show active" id="datosplanificacion" role="tabpanel">
        <input type="hidden" value={{ $planificaciones->tecnicosid ? 1 : 0 }} id="valores">
        <input type="hidden" value="" id="temasA" name="temasA">
        <div class="form-group row">
            <div class="col-lg-6">
                <label>Descripcion:</label>

                <input type="text" class="form-control " placeholder="Ingrese una descripcion" name="descripcion"
                    id="descripcion" autocomplete="off"
                    value="{{ old('descripcion', $planificaciones->descripcion) }}" />

                @if ($errors->has('descripcion'))
                    <span class="text-danger">{{ $errors->first('descripcion') }}</span>
                @endif
            </div>

            <div class="col-lg-6">
                <label>Técnicos:</label>
                <select class="form-control select2" id="tecnicosid" name="tecnicosid" {{ $disabled }}>
                    @if (count($listadoTecnicos) > 0)
                        @foreach ($listadoTecnicos as $tecnicosL)
                            @if ($tecnicosL->tecnicosid != 0)
                                <option value="{{ $tecnicosL->tecnicosid }}"
                                    {{ $tecnicosL->tecnicosid ==
                                    ($planificaciones->tecnicosid ? $planificaciones->tecnicosid : Auth::guard('tecnico')->user()->tecnicosid)
                                        ? 'selected'
                                        : '' }}>
                                    {{ $tecnicosL->nombres }}
                                </option>
                            @endif
                        @endforeach
                    @else
                        <option value="">
                            No existe ningún técnico
                        </option>
                    @endif
                </select>
                @if ($errors->has('tecnicosid'))
                    <span class="text-danger">{{ $errors->first('tecnicosid') }}</span>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-lg-6">
                <label>Clientes:</label>
                <select class="form-control select2" id="clientesid" name="clientesid">
                    @if (count($listadoClientes) > 0)
                        <option value="">
                            Escoja un Cliente
                        </option>
                        @foreach ($listadoClientes as $clientesL)
                            <option value="{{ $clientesL->clientesid }}"
                                {{ $clientesL->clientesid == old('clientesid', $planificaciones->clientesid) ? 'selected' : '' }}>
                                {{ $clientesL->razonsocial }}
                            </option>
                        @endforeach
                    @else
                        <option value="">
                            No hay clientes registrados
                        </option>
                    @endif

                </select>
                @if ($errors->has('clientesid'))
                    <span class="text-danger">{{ $errors->first('clientesid') }}</span>
                @endif
            </div>

            <div class="col-lg-6">
                <label>Productos:</label>
                <select class="form-control select2" id="productosid" name="productosid">
                    @if (count($listadoProductos) > 0)
                        <option value="">
                            Escoja un Producto
                        </option>
                        @foreach ($listadoProductos as $productosL)
                            <option value="{{ $productosL->productosid }}"
                                {{ $productosL->productosid == old('productosid', $planificaciones->productosid) ? 'selected' : '' }}>
                                {{ $productosL->descripcion }}
                            </option>
                        @endforeach
                    @else
                        <option value="">
                            No existe ningún Producto
                        </option>
                    @endif
                </select>
                @if ($errors->has('productosid'))
                    <span class="text-danger">{{ $errors->first('productosid') }}</span>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <div class="col-lg-6">
                <div>
                    <label>Colaboradores:</label>
                    <input class='tagify--outside' style="padding: 10px 0;" id="colaboradores" name="colaboradores"
                        value="{{ old('colaboradores', $planificaciones->colaboradores) }}">
                </div>
                @if ($errors->has('colaboradores'))
                    <span class="text-danger">{{ $errors->first('colaboradores') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="tab-pane fade" role="tabpanel" id="temasplanificacion">
        <table class="table table-sm table-bordered table-head-custom table-hover" id="temas">
            <thead>
                <tr>
                    <th>Categorias</th>
                    <th>Temas</th>
                    <th>accion</th>
                    <th>identificador</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <script>
        var valoresT = $("#valores").val();
        $(document).ready(function() {
            var input = document.querySelector('#colaboradores');
            var tagify = new Tagify(input, {
                dropdown: {
                    position: "input",
                    enabled: 0 // always opens dropdown when input gets focus
                }
            })

            $(document).on('change', '#productosid', function() {
                valoresT = 0
                var productosid = $(this).val();
                llenarTemas(productosid);
            });

            if (valoresT == 1) {
                var productosid = $("#productosid").val();
                llenarTemas(productosid);
            }

            $(document).on('change', '[name="categoriaCheck"]', function() {
                const idCheckboxSeleccionado = $(this).attr('id');
                const categoriaCheck = document.getElementById(idCheckboxSeleccionado);
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
                }
            });

            $(document).on('click', '.nombreCategoria', function() {
                const idnombreCategoria = $(this).attr('id');
                var numero = idnombreCategoria.split('-')[1];
                const filas = $('.checkCat-' + numero);
                filas.each(function() {
                    var fila = $(this).closest('tr');
                    fila.is(':hidden') ? fila.show() : fila.hide();

                });
            });

        });

        function llenarTemas(planificaciones) {
            if (planificaciones != "") {
                if ($.fn.DataTable.isDataTable('#temas')) {
                    $('#temas').DataTable().destroy();
                }
                const rutaTemas = `{{ route('planificaciones.temas', ['planificaciones' => 'valor']) }}`;
                const url = rutaTemas.replace('valor', planificaciones);
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
                            var valorCat = api.cell(i, 3).data();
                            if (last !== group) {
                                $(rows).eq(i).before(
                                    '<tr class="group" ><td colspan="1" class="nombreCategoria" id="nombreCategoria-' +
                                    valorCat + '">' + group +
                                    '</td><td><label class="checkbox  checkbox-success"><input class="checkboxList" name="categoriaCheck" type="checkbox" id="checkCat-' +
                                    valorCat + '"/> <span></span></label></td></tr>',
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
                        },
                        {
                            data: 'identificador',
                            name: 'categorias.categoriasid',
                            orderable: false,
                            searchable: false,
                            visible: false

                        },
                    ],
                    initComplete: function() {

                        if (valoresT == 0) {
                            const checkboxesList = document.querySelectorAll('.checkboxList');
                            checkboxesList.forEach(asignado => {
                                asignado.checked = true;
                            });
                        } else {

                            var temasCheck = @json($temasCheck->pluck('temasid'));
                            temasCheck.forEach(tema => {

                                $('#' + tema).prop('checked', true);
                            });

                        }

                    }
                });

                $('#formulario').submit(function(event) {
                    event.preventDefault();
                    temasasignados = '';

                    $(".checkboxTemas").each(function() {
                        if ($(this).prop('checked')) {
                            temasasignados = temasasignados + $(this).attr('id') + ';';
                        }
                    });

                    $('#temasA').val(temasasignados)

                    $(this).unbind('submit').submit();
                });



            }

        }
    </script>
@endsection
