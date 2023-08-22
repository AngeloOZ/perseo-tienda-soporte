<!-- delete Modal -->
<div id="delete-modal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form method="POST" id="delete-link">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h4 class="modal-title h6">Confirmar Eliminar</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mt-1">Esta seguro de eliminar el registro?</p>
                    <button type="button" class="btn btn-link mt-2" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary mt-2">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /.modal -->