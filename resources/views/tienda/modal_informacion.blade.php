<div id="delete-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h3">Datos de la empresa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                @switch($vendedor->distribuidoresid ?? 0)
                    @case(1)
                        <p class="font-size-h6">
                            <strong>Nombre:</strong> PERSEOALFA S.A.S. 🪪<br>
                            <strong>RUC:</strong> 2390625831001 🪪<br>
                            <strong>Correo:</strong> ventas.sto@perseo.ec 📧 <br>
                        </p>

                        <h3 class="font-size-h4 font-weight-bolder">Banco Pichincha 🏦</h3>
                        <p class="font-size-h6">
                            <strong>Tipo cuenta:</strong> Corriente<br>
                            <strong>Número de Cuenta:</strong> 2100272144 <br>
                        </p>

                        <h3 class="font-size-h4 font-weight-bolder">Banco Produbanco 🏦</h3>
                        <p class="font-size-h6">
                            <strong>Tipo cuenta:</strong> Ahorros<br>
                            <strong>Número de Cuenta:</strong> 12060299644 <br>
                        </p>
                    @break

                    @case(2)
                        <p class="font-size-h6">
                            <strong>Nombre:</strong> Perseo Soft S.A. 🪪<br>
                            <strong>RUC:</strong> 1792765781001 🪪<br>
                        </p>
                        <h3 class="font-size-h4 font-weight-bolder">Banco Pichincha 🏦</h3>
                        <p class="font-size-h6">
                            <strong>Tipo cuenta:</strong> Corriente <br>
                            <strong>Número de Cuenta:</strong> 2100272341 <br>
                        </p>
                    @break

                    @default
                        <p class="font-size-h6">
                            No se ha configurado la información bancaria de la empresa.
                        </p>
                @endswitch
                <div class="text-center">
                    <button type="button" class="btn btn-primary mt-2" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
