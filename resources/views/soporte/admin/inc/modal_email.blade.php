<div class="modal fade" id="modalEmail" tabindex="-1" role="dialog" aria-labelledby="modalEmail" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Registrar actividad </h5>
                <button type="button" id="closeModal" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <form id="formEmail">
                    <div class=" w-100 px-4 py-1 mt-2">
                        <div class="form-group row">
                            <div class="col-12 mb-3 col-md-3 mb-md-0">
                                <label for="rolCliente">Cliente</label>
                                <select class="form-control" id="rolCliente">
                                    <option value="">No enviar</option>
                                    <option value="{{ $ticket->correo }}" selected>Enviar</option>
                                </select>
                            </div>

                            @if (Auth::user()->rol == 7 || Auth::user()->rol == 6)
                                <div class="col-12 mb-3 col-md-3 mb-md-0">
                                    <label for="rolCliente">Técnico</label>
                                    <select class="form-control" id="rolTecnico">
                                        <option value="">No enviar</option>
                                        <option value="{{ $tecnicoAsignado->correo }}" selected>Enviar</option>
                                    </select>
                                </div>
                            @endif


                            @if (Auth::user()->rol != 6)
                                <div class="col-12 mb-3 col-md-3 mb-md-0">
                                    <label for="rolDesarrollador">Desarrollador</label>
                                    <select class="form-control" id="rolDesarrollador">
                                        <option value="" selected>No enviar</option>
                                        @foreach ($desarrolladores as $desarrollador)
                                            <option value="{{ $desarrollador->correo }}">{{ $desarrollador->nombres }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if (Auth::user()->rol == 5 || Auth::user()->rol == 6)
                                <div class="col-12 mb-3 col-md-3 mb-md-0">
                                    <label for="rolSupervisor">Supervisor</label>
                                    <select class="form-control" id="rolSupervisor">
                                        <option value="" selected>No enviar</option>
                                        @foreach ($supervisores as $supervisor)
                                            <option value="{{ $supervisor->correo }}">{{ $supervisor->nombres }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-12 mb-3 col-md-3 mb-md-0">
                                <label>Enviar correo</label>
                                <span class="switch switch-outline switch-icon switch-success">
                                    <label>
                                        <input type="checkbox" id="checkEnviarCorreo" checked="checked" />
                                        <span></span>
                                    </label>
                                </span>
                            </div>

                        </div>
                    </div>
                </form>
                {{-- <textarea name="kt-ckeditor-1" id="kt-ckeditor-1" style="width: 100%;"></textarea> --}}
                <textarea class="summernote" id="kt_summernote_1"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="limpiarCampos()">Limpiar campos</button>
                <button type="button" class="btn btn-light-primary font-weight-bold"
                    data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary font-weight-bold" id="btnSendMail">Enviar</button>
            </div>
        </div>
    </div>
</div>
