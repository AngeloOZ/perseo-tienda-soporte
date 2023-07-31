<div class="accordion accordion-solid accordion-toggle-plus" id="accordionExample6">
    <div class="card">
        <div class="card-header" id="headingOne6">
            <div class="card-title" data-toggle="collapse" data-target="#collapseOne6">
                <i class="fas fa-headset"></i> Soportes anteriores
            </div>
        </div>
        <div id="collapseOne6" class="collapse show" data-parent="#accordionExample6">
            <div class="card-body">
                <div class="timeline timeline-6 mt-3">
                    @foreach ($historialTickets as $item)
                        <div class="timeline-item align-items-start">
                            <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                                {{ get_date_time($item->fecha_asignacion, true) }}
                            </div>

                            <div class="timeline-badge">
                                <i class="fa fa-genderless text-primary icon-xl"></i>
                            </div>

                            <div class="font-weight-mormal font-size-lg timeline-content pl-3">
                                <p><strong>{{ get_date_time($item->fecha_asignacion) }}</strong></p>
                                <p class="my-1"><strong>Técnico:</strong> {{ $item->tecnico }}</p>
                                <p class="my-1"><strong>Motivo:</strong> {{ $item->motivo }}</p>
                                <a href="{{ route('soporte.editar', $item->ticketid) }}" target="_blank">ver más</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header" id="headingTwo6">
            <div class="card-title collapsed" data-toggle="collapse" data-target="#collapseTwo6">
                <i class="fas fa-user-graduate"></i> Capacitaciones
            </div>
        </div>
        <div id="collapseTwo6" class="collapse" data-parent="#accordionExample6">
            <div class="card-body">
                <div class="timeline timeline-6 mt-3">
                    @foreach ($historialCapacitaciones as $item)
                        <div class="timeline-item align-items-start">
                            <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                                {{ get_date_time($item->fecha_agendado, true) }}
                            </div>

                            <div class="timeline-badge">
                                <i class="fa fa-genderless text-primary icon-xl"></i>
                            </div>

                            <div class="font-weight-mormal font-size-lg timeline-content pl-3">
                                <p><strong>{{ get_date_time($item->fecha_agendado) }}</strong></p>
                                <p class="my-1"><strong>Técnico:</strong> {{ $item->tecnico }}</p>
                                <p class="my-1"><strong>Tipo:</strong> {{ $item->tipo }}</p>
                                <a href="{{ route('sop.editar_soporte_especial', $item->soporteid) }}" target="_blank">ver más</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
