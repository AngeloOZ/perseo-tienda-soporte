@php
    function get_date_time($fecha, $getHora = false)
    {
        if ($getHora) {
            return date('H:i', strtotime($fecha));
        }
        return date('d-m-Y', strtotime($fecha));
    }
@endphp

<style>
    :root{
        --color-1: #3699FF;
        --color-2: #FFF;
    }
    .loader-activity {
        width: 175px;
        height: 80px;
        display: block;
        margin: auto;
        background-image: radial-gradient(circle 25px at 25px 25px, var(--color-1) 100%, transparent 0), radial-gradient(circle 50px at 50px 50px, var(--color-1) 100%, transparent 0), radial-gradient(circle 25px at 25px 25px, var(--color-1) 100%, transparent 0), linear-gradient(var(--color-1) 50px, transparent 0);
        background-size: 50px 50px, 100px 76px, 50px 50px, 120px 40px;
        background-position: 0px 30px, 37px 0px, 122px 30px, 25px 40px;
        background-repeat: no-repeat;
        position: relative;
        box-sizing: border-box;
    }

    .loader-activity::after {
        content: '';
        left: 50%;
        bottom: 0;
        transform: translate(-50%, 0);
        position: absolute;
        border: 15px solid transparent;
        border-top-color: var(--color-2);
        box-sizing: border-box;
        animation: fadePushActivity 1s linear infinite;
    }

    .loader-activity::before {
        content: '';
        left: 50%;
        bottom: 30px;
        transform: translate(-50%, 0);
        position: absolute;
        width: 15px;
        height: 15px;
        background: var(--color-2);
        box-sizing: border-box;
        animation: fadePushActivity 1s linear infinite;
    }

    @keyframes fadePushActivity {
        0% {
            transform: translate(-50%, -15px);
            opacity: 0;
        }

        50% {
            transform: translate(-50%, 0px);
            opacity: 1;
        }

        100% {
            transform: translate(-50%, 15px);
            opacity: 0;
        }
    }
</style>

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
    <div id="contenedor-actividades">
        <div id="id-loader-activity">
            <span class="loader-activity"></span>
        </div>
    </div>
    @if ($ticket->estado >= 3)
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
