<div class="w-100">
    <div class="col-md-12">
        <!--begin::Card-->
        <div class="row">
            
            <div class="col-lg-4 mt-0 mt-md-0">
                <!--begin::Card-->
                <div class="card card-card-stretch mt-3 mt-md-0">
                    <div class="card-header mb-0 pb-0 pt-4">
                        <div class="card-title">
                            <h6 class="card-label">Foto</h6>
                        </div>
                    </div>

                    <div class="card-body text-center">
                        <div class="text-center">
                            <div class="image-input w-75" id="kt_image_3">
                                <div class="image-input-wrapper w-100"
                                    style="background-image: url(data:image/png;base64,{{ $firma->foto }}); background-size: contain;background-position: center;">
                                </div>

                                <div>
                                    <label
                                        class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                        data-action="change" data-toggle="tooltip" title=""
                                        data-original-title="Cambiar Imagen">
                                        <i class="fa fa-pen icon-sm text-muted"></i>
                                        <input type="file" name="foto" id="foto"
                                            accept=".png, .jpg, .jpeg" />
                                        <input type="hidden" />
                                    </label>

                                    <span
                                        class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                        data-action="cancel" data-toggle="tooltip" title="Cancelar Imagen">
                                        <i class="ki ki-bold-close icon-xs text-muted"></i>
                                    </span>
                                </div>
                            </div>

                        </div>
                        <span class="text-danger d-none " id="mensajeFoto"> Ingrese una foto</span>
                    </div>
                </div>
                <!--end::Card-->
            </div>

            <div class="col-lg-4 mt-5 mt-md-0">
                <!--begin::Card-->
                <div class="card card-stretch mt-3 mt-md-0">
                    <div class="card-header mb-0 pb-0 pt-4">
                        <div class="card-title ">
                            <h6 class="card-label">Cédula <small>anverso</small></h6>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="text-center">
                            <div class="image-input  w-75" id="kt_image_1">
                                <div class="image-input-wrapper w-100 "
                                    style="background-image: url(data:image/png;base64,{{ $firma->foto_cedula_anverso }}); background-size: contain; background-position: center;  ">
                                </div>

                                <label
                                    class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                    data-action="change" data-toggle="tooltip" title=""
                                    data-original-title="Cambiar Imagen">
                                    <i class="fa fa-pen icon-sm text-muted"></i>
                                    <input type="file" name="foto_cedula_anverso" id="cedula"
                                        accept=".png, .jpg, .jpeg" />
                                    <input type="hidden" />
                                </label>

                                <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                    data-action="cancel" data-toggle="tooltip" title="Cancelar Imagen">
                                    <i class="ki ki-bold-close icon-xs text-muted"></i>
                                </span>
                            </div>
                        </div>
                        <span class="text-danger d-none" id="mensajeAnverso"> Ingrese una imagen de la cédula</span>

                    </div>
                </div>
                <!--end::Card-->
            </div>
            
            <div class="col-lg-4 mt-5 mt-md-0">
                <!--begin::Card-->
                <div class="card card-card-stretch mt-3 mt-md-0">
                    <div class="card-header mb-0 pb-0 pt-4">
                        <div class="card-title">
                            <h6 class="card-label">Cédula <small> reverso</small></h6>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="text-center">
                            <div class="image-input  w-75 " id="kt_image_2">
                                <div class="image-input-wrapper w-100"
                                    style="background-image: url(data:image/png;base64,{{ $firma->foto_cedula_reverso }}); background-size: contain;background-position: center;">
                                </div>

                                <label
                                    class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                    data-action="change" data-toggle="tooltip" title=""
                                    data-original-title="Cambiar Imagen">
                                    <i class="fa fa-pen icon-sm text-muted"></i>
                                    <input type="file" name="foto_cedula_reverso" id="reverso"
                                        accept=".png, .jpg, .jpeg" />
                                    <input type="hidden" />
                                </label>

                                <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow"
                                    data-action="cancel" data-toggle="tooltip" title="Cancelar Imagen">
                                    <i class="ki ki-bold-close icon-xs text-muted"></i>
                                </span>
                            </div>

                        </div>
                        <span class="text-danger d-none" id="mensajeReverso"> Ingrese una imagen del reverso de la
                            cédula</span>
                    </div>
                </div>
                <!--end::Card-->
            </div>
        </div>

    </div>
</div>
