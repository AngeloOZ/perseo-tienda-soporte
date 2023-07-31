<div class="w-100">
    <div class="col-md-12">
        <div class="natural">
            <h6 class="card-label"> Con Ruc </h6>

            <div class="form-check-inline">
                <input type="radio" name="conruc" id="ruc1" value="1"  style="height:18px; width:18px;" checked>
                <label class="form-check-label ml-2">
                    Si
                </label>
            </div>
            <div class="form-check-inline">
                <input type="radio" name="conruc" id="ruc2" value="2"  style="height:18px; width:18px;">
                <label class="form-check-label ml-2">
                    No
                </label>
            </div>
        </div>
        <div class="row mt-5 verificarRuc">
            <div class="col-lg-12 mt-4">
                <!--begin::Card-->
                <div class="card card-stretch">
                    <div class="card-header mb-0 pb-0 pt-4">
                        <div class="card-title">
                            <h6 class="card-label"> RUC <small>Escaneado o descargado del sri
                                    en
                                    PDF</small></h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="file" class="form-control" name="doc_ruc" accept="application/pdf"
                            id="documento" />
                        <span class="text-danger d-none" id="mensajeArchivoRuc"> Ingrese el archivo del RUC</span>
                         <p class="text-danger d-none" id="mensajeArchivoRuc2"> El tamaño del pdf debe ser menor a los
                            2MB</p>
                    </div>
                </div>
                <!--end::Card-->
            </div>

            <div class="col-lg-12 mt-4 legal">
                <!--begin::Card-->
                <div class="card card-stretch">
                    <div class="card-header mb-0 pb-0 pt-4">
                        <div class="card-title">
                            <h6 class="card-label"> Constitución de la empresa <small> o Estatutos</small></h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="file" class="form-control" name="doc_constitucion" accept="application/pdf"
                            id="constitucion" />
                        <span class="text-danger d-none" id="mensajeArchivoConstitucion"> Ingrese el Archivo de
                            Constitucion de la Empresa</span>
                             <p class="text-danger d-none" id="mensajeArchivoConstitucion2"> El tamaño del pdf debe ser menor a los
                            4MB</p>
                    </div>
                </div>
                <!--end::Card-->
            </div>
        </div>

        <div class="row mt-5 legal">
            <div class="col-lg-12 mt-4 ">
                <!--begin::Card-->
                <div class="card card-stretch">
                    <div class="card-header mb-0 pb-0 pt-4">
                        <div class="card-title">
                            <h6 class="card-label"> Nombramiento del representante legal </h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="file" class="form-control" name="doc_nombramiento" accept="application/pdf"
                            id="nombramiento" />
                        <span class="text-danger d-none" id="mensajeArchivoNombramiento"> Ingrese el Archivo de
                            Nombramiento</span>
                             <p class="text-danger d-none" id="mensajeArchivoNombramiento2"> El tamaño del pdf debe ser menor
                            a los
                            2MB</p>
                    </div>
                </div>
                <!--end::Card-->
            </div>

            <div class="col-lg-12 mt-4">
                <!--begin::Card-->
                <div class="card card-stretch">
                    <div class="card-header mb-0 pb-0 pt-4">
                        <div class="card-title">
                            <h6 class="card-label"> Aceptación del nombramiento del Representante legal</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="file" class="form-control" name="doc_aceptacion" accept="application/pdf"
                            id="aceptacion" />
                                <p class="text-danger d-none" id="mensajeArchivoAceptacion"> El tamaño del pdf debe ser menor a
                            los
                            2MB</p>
                    </div>

                </div>
                <!--end::Card-->
            </div>
        </div>

    </div>
</div>
