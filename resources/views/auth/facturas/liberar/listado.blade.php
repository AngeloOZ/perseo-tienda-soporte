<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Detalle de producto</th>
            <th scope="col">Estado Liberación</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productos as $key => $item)
            <tr>
                <td scope="row">{{ $key + 1 }}</td>
                <td>{{ $item->descripcion }}</td>
                <td>
                    @switch($item->liberado)
                        @case(0)
                            <span class="label label-xl label-danger label-pill label-inline">Por liberar</span>
                        @break

                        @case(1)
                            <span class="label label-xl label-success label-pill label-inline">Liberado</span>
                        @break

                        @case(2)
                            <span class="label label-xl label-info label-pill label-inline">Liberacion manual</span>
                        @break
                        @case(3)
                            <span class="label label-xl label-secondary label-pill label-inline">No aplica</span>
                        @break
                    @endswitch
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
