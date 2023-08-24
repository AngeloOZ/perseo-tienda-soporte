@php
    use App\Constants\ConstantesTecnicos;
    
    $clientes = App\Models\Clientes::select('clientesid', 'razonsocial')
        ->where('distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid)
        ->where('estado', 1)
        ->get();
    
    $productos2 = App\Models\Productos2::select('productosid', 'descripcion')->get();
    
    $listadoTecnicos = App\Models\Tecnicos::select('tecnicosid', 'nombres')
        ->where('rol', ConstantesTecnicos::ROL_TECNICOS)
        ->where('distribuidoresid', Auth::guard('tecnico')->user()->distribuidoresid)
        ->where('estado', 1)
        ->get();
@endphp
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
                    <label for="">Cliente</label>
                    <select class="form-control select2" id="clientesid" name="clientesid"
                        @if ((isset($sesiones->fechainicio) && $sesiones->fechainicio != null) || $sesiones->fechafin != null) disabled @endif onchange="cambiarPlanificaciones();">
                        <option value="">
                            Escoja un Cliente
                        </option>
                        @if (count($clientes) > 0)
                            @foreach ($clientes as $clientesL)
                                @if ($clientesL->clientesid != 0)
                                    <option value="{{ $clientesL->clientesid }}"
                                        {{ $clientesL->clientesid == old('clientesid', $sesiones->clientesid) ? 'selected' : '' }}>
                                        {{ $clientesL->razonsocial }}
                                    </option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                    @if ($errors->has('clientesid'))
                        <span class="text-danger">{{ $errors->first('clientesid') }}</span>
                    @endif
                </div>
                <div class="col-lg-6">
                    <label for="">Planificaciones</label>
                    <select class="form-control select2" id="planificacionesid" name="planificacionesid"
                        @if ((isset($sesiones->fechainicio) && $sesiones->fechainicio != null) || $sesiones->fechafin != null) disabled @endif>
                        <option value="">
                            Escoja una planificación
                        </option>

                    </select>
                    @if ($errors->has('planificacionesid'))
                        <span class="text-danger">{{ $errors->first('planificacionesid') }}</span>
                    @endif
                </div>

            </div>

            <div class="form-group row">
                <div class="col-lg-6">
                    <label for="">Producto</label>
                    <input class="form-control" type="text" disabled id="productosid" name="productosid">
                </div>

                <div class="col-lg-6">
                    <label>Técnicos:</label>
                    <select class="form-control select2" id="tecnicosid" name="tecnicosid"
                        @if ((isset($sesiones->fechainicio) && $sesiones->fechainicio != null) || $sesiones->fechafin != null) disabled @endif>
                        @if (count($listadoTecnicos) > 0)

                            @foreach ($listadoTecnicos as $tecnicosL)
                                @if ($tecnicosL->tecnicosid != 0)
                                    <option value="{{ $tecnicosL->tecnicosid }}"
                                        {{ $tecnicosL->tecnicosid ==
                                        ($sesiones->tecnicosid ? $sesiones->tecnicosid : Auth::guard('tecnico')->user()->tecnicosid)
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
                    <label for="">Enlace</label>
                    <input type="text" class="form-control" name="enlace" id="enlace" autocomplete="off"
                        value="{{ old('enlace', $sesiones->enlace) }}">
                    @if ($errors->has('enlace'))
                        <span class="text-danger">{{ $errors->first('enlace') }}</span>
                    @endif
                </div>


                <div class="col-lg-6">
                    <label for="">Aceptado por el cliente</label>
                    <span class="switch switch-outline switch-icon switch-primary">
                        <label>
                            <input type="checkbox" name="revisado" id="revisado" disabled
                                @if ($sesiones->revisioncliente == 1) checked @endif />
                            <span></span>
                        </label>
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-6 col-lg-5 col-xl-6">
                    <label for="">Ingrese descripción a realizar:</label>
                    <div class="d-flex">
                        <textarea class="form-control" name="descripcion" id="descripcion" cols="40%" rows="5"> {{ old('descripcion', $sesiones->descripcion) }}</textarea>
                    </div>
                    <div>
                        @if ($errors->has('descripcion'))
                            <span class="text-danger">{{ $errors->first('descripcion') }}</span>
                        @endif
                    </div>
                </div>

                <div
                    class="d-flex col-6 col-md-3 col-lg-4 col-xl-4 mt-3 md-mt-0 align-items-center justify-content-center ">
                    <label style="font-weight: bold; ">Tiempo Ocupado: </label>
                    <span class="ml-2 mb-md-2 "
                        id="tiempoOcupado-{{ $sesiones->sesionesid }}">{{ $sesiones->suma }}</span>
                    <span class="ml-1" id="spinner">
                    </span>
                </div>
                <div
                    class="d-flex col-6 col-md-3 col-lg-3 col-xl-2 mt-3 md-mt-0 align-items-center justify-content-end ">
                    <button type="button" class="btn btn-primary inicio"
                        @if (!$sesiones->sesionesid || $sesiones->fechainicio != null) disabled="disabled" @endif
                        id="inicio-{{ $sesiones->sesionesid }}">
                        Iniciar
                    </button>
                    <button type="button" class="btn btn-primary ml-3 fin"
                        @if (!$sesiones->fechainicio || $sesiones->fechafin != null) disabled="disabled" @endif
                        id="fin-{{ $sesiones->sesionesid }}">
                        Fin
                    </button>
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
                    <th>accion</th>
                    <th>identificador</th>
                    <th>Calificación</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


@section('script')
    <script>
        $(document).ready(function() {
            cambiarPlanificaciones();

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
        })

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

                                $('#tecnicosid').val(data.tecnicosid);
                                $('#tecnicosid').trigger('change');

                                $('#planificacionesid').append(`
                                    <option class="${data.producto}-${data.productosid}" value="${data.planificacionesid}" ${seleccionado} >${data.descripcion}</option>
                                `)
                            });
                            $('#planificacionesid').trigger('change');
                        }
                    }
                })
            }
        }

        $('#planificacionesid').on('change', function() {
            let opcion = $(this).find('option:selected');
            let clase = opcion.attr('class');
            var partes = clase.split('-');
            $('#productosid').val(partes[0]);
            llenarTemas(partes[1]);
        });

        $(".inicio").click(function(event) {
            let botoninicio = event.target.id;

            var arrayBoton = botoninicio.split('-');

            $.post('{{ route('sesiones.ingresarFechaInicio') }}', {
                _token: '{{ csrf_token() }}',
                idsesiones: arrayBoton[1],

            }, function(data) {
                if (data == 1) {
                    $('#inicio-' + arrayBoton[1]).prop('disabled',
                        true);
                    $('#fin-' + arrayBoton[1]).prop('disabled',
                        false);
                    $('#clientesid').prop('disabled',
                        true);
                    $('#tecnicosid').prop('disabled',
                        true);
                    $('#planificacionesid').prop('disabled',
                        true);

                } else {
                    $('#inicio-' + arrayBoton[1]).prop('disabled',
                        false);
                }

            });
        });

        $(".fin").click(function(event) {
            let botonfin = event.target.id;
            var arrayBoton = botonfin.split('-');
            $("#spinner").addClass("spinner spinner-success spinner-left");
            $('#fin-' + arrayBoton[1]).prop('disabled',
                true);
            $.post('{{ route('sesiones.ingresarFechaFin') }}', {
                _token: '{{ csrf_token() }}',
                idsesiones: arrayBoton[1],

            }, function(data) {
                if (data != 'a') {

                    let tiempoOcupado = document.getElementById('tiempoOcupado-' +
                        arrayBoton[1]);
                    var TemplateTiempoOcupado = `<span>${data}</span>`;

                    tiempoOcupado.innerHTML = TemplateTiempoOcupado;
                    $('#enlace').prop('readonly',
                        false);

                    var checkboxes = $('#temas').find('input[type="checkbox"]');
                    checkboxes.prop('disabled', true);

                } else {
                    $('#fin-' + arrayBoton[1]).prop('disabled',
                        false);
                }
                $("#spinner").removeClass("spinner spinner-success spinner-left");
            });
        });

        function llenarTemas(planificaciones) {
            if (planificaciones != "") {
                if ($.fn.DataTable.isDataTable('#temas')) {
                    $('#temas').DataTable().destroy();
                }

                const rutaTemas = `{{ route('planificaciones.temas', ['planificaciones' => 'valor']) }}"`;
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
                        {
                            data: null,
                            name: '',
                            orderable: false,
                            searchable: false,
                            visible: true,
                            render: function(data, type, row, meta) {
                                var temaId = row.temasid;
                                var labelContent = '<label id="label-' + temaId +
                                    '" for="valorTema"></label>';

                                return labelContent;
                            }
                        }
                    ],
                    initComplete: function() {
                        const rutaDetalles =
                            `{{ route('sesiones.recuperardetalles', ['detalles' => 'valor']) }}"`;
                        const url = rutaDetalles.replace('valor', $('#planificacionesid').val());
                        const fin = `{{ $sesiones->fechafin }}`;
                        var checkboxes = $('#temas').find('input[type="checkbox"]');
                        var sesiones = {!! json_encode($sesiones) !!};

                        $.get(url, function(data) {
                            if (data.length > 0) {
                                data.forEach(tema => {
                                    $('#' + tema.temasid).prop('checked', true);
                                    var contenido = "";
                                    switch (tema.calificacioncliente) {
                                        case 0, '0':
                                            contenido = "No calificado";

                                            break;
                                        case 1, '1':
                                            contenido = "Aprendí";

                                            break;
                                        case 2, '2':
                                            contenido = "Ya lo sabía";
                                            break;
                                        default:
                                            contenido = "";

                                    }
                                    $('#label-' + tema.temasid).text(contenido);
                                });
                            } else {
                                checkboxes.prop('disabled', true);
                            }

                            if (fin || sesiones.length == 0) {
                                checkboxes.prop('disabled', true);
                            }
                        }).fail(function() {
                            console.error("Error en la solicitud");
                        });
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

                    $('#tsesion').val(temasasignados)
                    $(this).unbind('submit').submit();
                });
            }
        }
    </script>
@endsection
