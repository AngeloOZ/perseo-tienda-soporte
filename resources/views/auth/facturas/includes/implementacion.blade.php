<div class="tab-pane fade" id="implementacion-1" role="tabpanel" aria-labelledby="implementacion-tab-1">
    <div class="card-body">

        <div class="">
            <h3 class="card-title align-items-start flex-column">
                <span class="font-weight-bolder text-dark">Implementación</span>
            </h3>
        </div>
        <div class="form-group row mb-8">
            <div class="col-12 mt-2 col-lg-6 mt-lg-0">
                <label>Estado:</label>
                <input type="text" class="form-control" disabled value="{{ $soporte->estado }}" />
            </div>
            <div class="col-12 mt-2 col-lg-6 mt-lg-0">
                <label>Técnico asignado:</label>
                <input type="text" class="form-control" disabled value="{{ $soporte->tecnico }}" />
            </div>
        </div>

        <div class="p-1">
            @include('soporte.admin.tecnico.demos.inc.list_actividades')
        </div>
    </div>
</div>
