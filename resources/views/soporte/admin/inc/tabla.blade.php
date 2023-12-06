<!--begin: Search Form-->
<table class="table table-sm table-bordered table-head-custom table-hover text-center" id="kt_datatable">
    <thead>
        <tr>
            <th class="no-exportar">#</th>
            <th>No: Ticket</th>
            <th>RUC</th>
            <th>Razón social</th>
            <th>Correo</th>
            <th>Whatsapp</th>
            <th>Estado</th>
            <th>Fecha ingreso</th>
            @if (Auth::guard('tecnico')->user()->rol == 7)
                <th>Tiempo contactado</th>
            @endif
            <th>Tiempo activo</th>
            <th class="no-exportar">Acciones</th>
        </tr>
    </thead>
</table>
