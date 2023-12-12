@php
    use App\Constants\ConstantesTecnicos;
    if (count($actividades) == 0) {
        $actividades = App\Models\ActividadTicket::where('ticketid', $ticket->ticketid)->get();
    }

    function get_date_time($fecha, $getHora = false)
    {
        if ($getHora) {
            return date('H:i', strtotime($fecha));
        }
        return date('d-m-Y', strtotime($fecha));
    }

    $estados = ConstantesTecnicos::obtenerEstadosTickets();
@endphp
<!--begin::Header-->
<div class="">
    <h3 class="card-title align-items-start flex-column">
        <span class="font-weight-bolder text-dark">Registro de actividad</span>
    </h3>
</div>

<!--begin::Timeline-->
<div class="timeline timeline-6 mt-3">
    <div class="timeline-item align-items-start">
        <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
            {{ get_date_time($ticket->fecha_creado, true) }}</div>
        <div class="timeline-badge">
            <i class="fa fa-genderless text-success icon-xl"></i>
        </div>
        <div class="font-weight-mormal font-size-lg timeline-content pl-3">
            <p>
                <strong>{{ get_date_time($ticket->fecha_creado) }}: </strong>
                Fecha de creación
            </p>
        </div>
    </div>
    <div class="timeline-item align-items-start">
        <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
            {{ get_date_time($ticket->fecha_asignacion, true) }}</div>
        <div class="timeline-badge">
            <i class="fa fa-genderless text-info icon-xl"></i>
        </div>
        <div class="font-weight-mormal font-size-lg timeline-content pl-3">
            <p>
                <strong>{{ get_date_time($ticket->fecha_asignacion) }}: </strong>
                Fecha de asignación
            </p>
        </div>
    </div>
    @foreach ($actividades as $actividad)
        <div class="timeline-item align-items-start">
            <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                {{ get_date_time($actividad->fecha_creado, true) }}</div>
            <div class="timeline-badge">
                <i class="fa fa-genderless text-primary icon-xl"></i>
            </div>
            <div class="timeline-content font-weight-mormal font-size-lg pl-3">
                <p>
                    <strong>{{ get_date_time($actividad->fecha_creado) }}: </strong>
                    @foreach (json_decode($actividad->dirigido_a) as $key => $user)
                        <span class="label font-weight-bold label-lg  label-light-info label-inline">
                            <strong>{{ strtoupper($key) }}: </strong>{{ $user }}
                        </span>
                    @endforeach
                </p>
                <div>
                    {!! $actividad->contenido !!}
                </div>
            </div>
        </div>
    @endforeach
    @if ($ticket->estado >= $estados['desarrollo']->id)
        <div class="timeline-item align-items-start">
            <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                {{ get_date_time($ticket->fecha_modificado, true) }}</div>
            <div class="timeline-badge">
                <i class="fa fa-genderless text-danger icon-xl"></i>
            </div>
            <div class="timeline-content font-weight-mormal font-size-lg pl-3">
                <strong>{{ get_date_time($ticket->fecha_modificado) }}: </strong> Ticket cerrado
            </div>
        </div>
    @endif
</div>
