<div class="tab-pane fade" id="solicitudEstado" role="tabpanel" aria-labelledby="archivos-tab-1">
    <div class="form-group row d-flex align-items-center">
        <div class="col-8">
            <h2 class="font-size-h2 m-0 p-0">Estado de la solicitud en linea</h2>
        </div>
        @if ($firma->uanatacaid == null)
            <div class="col-4 text-right">
                <a href="{{ route('firma.subirapi', $firma->firmasid) }}" id="btnSendDocs" class="btn btn-danger">Enviar documentos</a>
            </div>
        @endif
    </div>
    <div class="form-group row">
        <div class="col-12 mt-2 col-md-6">
            <label>Tipo de solicitud</label>
            <input type="text" class="form-control" placeholder="" value="{{ $estado_solicitud["tipo_solicitud"] ?? "" }}" readonly />
        </div>
        <div class="col-12 mt-2 col-md-6">
            <label>Identificación</label>
            <input type="text" class="form-control" placeholder="" value="{{ $estado_solicitud["documento"] ?? "" }}" readonly />
        </div>
        <div class="col-12 mt-2 col-md-6">
            <label>Nombre completo</label>
            <input type="text" class="form-control" placeholder="" value="{{ $estado_solicitud["nombre_completo"] ?? "" }}" readonly />
        </div>
        <div class="col-12 mt-2 col-md-6">
            <label>Estado</label>
            <input type="text" class="form-control" placeholder="" value="{{ $estado_solicitud["estado"] ?? "" }}" readonly />
        </div>
        <div class="col-12 mt-2 col-md-6">
            <label>Validez</label>
            <input type="text" class="form-control" placeholder="" value="{{ $estado_solicitud["validez"] ?? "" }}" readonly />
        </div>
        <div class="col-12 mt-2 col-md-6">
            <label>Fecha de registro</label>
            <input type="text" class="form-control" placeholder="" value="{{ $estado_solicitud["fecha_registro"] ?? "" }}" readonly />
        </div>
    </div>
</div>
