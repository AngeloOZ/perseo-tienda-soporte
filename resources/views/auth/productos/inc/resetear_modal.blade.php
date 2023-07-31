<div id="resetear-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form method="POST" action="{{ route('productos.resetear_precios') }}">
                @method('PUT')
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title h6">Restaurar precios</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de regresar los precios a por defecto?</p>
                    <div class="text-right">
                        <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary mt-2">Aplicar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
