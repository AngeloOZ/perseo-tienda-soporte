@php
    function get_date_time($fecha, $getHora = false)
    {
        if ($getHora) {
            return date('H:i', strtotime($fecha));
        }
        return date('d-m-Y', strtotime($fecha));
    }
    $actividades = json_decode($soporte->actividades) ?? [];
@endphp

<div class="">
    <h3 class="card-title align-items-start flex-column">
        <span class="font-weight-bolder text-dark">Registro de actividad</span>
    </h3>
</div>

<!--begin::Timeline-->
<div class="timeline timeline-6 mt-3">
    <div class="timeline-item align-items-start">
        <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
            {{ get_date_time($soporte->fecha_creacion, true) }}</div>
        <div class="timeline-badge">
            <i class="fa fa-genderless text-success icon-xl"></i>
        </div>
        <div class="font-weight-mormal font-size-lg timeline-content pl-3">
            <p>
                <strong>{{ get_date_time($soporte->fecha_creacion) }}: </strong>
                Fecha de creación
            </p>
        </div>
    </div>
    @if ($soporte->fecha_iniciado)
        <div class="timeline-item align-items-start">
            <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                {{ get_date_time($soporte->fecha_iniciado, true) }}</div>
            <div class="timeline-badge">
                <i class="fa fa-genderless text-info icon-xl"></i>
            </div>
            <div class="font-weight-mormal font-size-lg timeline-content pl-3">
                <p>
                    <strong>{{ get_date_time($soporte->fecha_iniciado) }}: </strong>
                    Fecha de inicio
                </p>
            </div>
        </div>
    @endif

    @foreach ($actividades as $actividad)
        <div class="timeline-item align-items-start">
            <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                {{ get_date_time($actividad->fecha, true) }}</div>
            <div class="timeline-badge">
                <i class="fa fa-genderless text-primary icon-xl"></i>
            </div>
            <div class="timeline-content font-weight-mormal font-size-lg pl-3">
                <p>
                    <strong>{{ get_date_time($actividad->fecha) }}</strong>
                    <span class="label font-weight-bold label-lg  label-light-info label-inline">
                        {{ $actividad->escritor }}
                    </span>
                </p>
                <div>
                    {!! $actividad->contenido !!}
                </div>
            </div>
        </div>
    @endforeach
    @if ($soporte->estado >=4 && $soporte->fecha_finalizado != null)
        <div class="timeline-item align-items-start">
            <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                {{ get_date_time($soporte->fecha_finalizado, true) }}</div>
            <div class="timeline-badge">
                <i class="fa fa-genderless text-danger icon-xl"></i>
            </div>
            <div class="timeline-content font-weight-mormal font-size-lg pl-3">
                <strong>{{ get_date_time($soporte->fecha_finalizado) }}: </strong> Soporte cerrado
            </div>
        </div>
    @endif
</div>
