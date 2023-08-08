<script>
    obtenerListadoActividades();

    async function obtenerListadoActividades() {
        const loader = document.getElementById("id-loader-activity");

        const {
            data: actividaes
        } = await axios.get("{{ route('soporte.obtener.actividades', $ticket->ticketid) }}");

        loader.remove();

        const contenedor = document.getElementById("contenedor-actividades");
        const fragment = document.createDocumentFragment();

        actividaes.forEach(actividad => {
            const remitentes = JSON.parse(actividad.dirigido_a);
            let remitentesHTML = "";

            for (const key in remitentes) {
                remitentesHTML += `<span class="label font-weight-bold label-lg  label-light-info label-inline">
                    <strong>${key.toUpperCase()}: </strong>${remitentes[key]}`
            }

            const DIV = document.createElement("div");
            DIV.classList.add("timeline-item", "align-items-start");
            DIV.innerHTML = `
                <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                    ${getDateTime(actividad.fecha_creado, true)}
                </div>
                <div class="timeline-badge">
                    <i class="fa fa-genderless text-primary icon-xl"></i>
                </div>
                <div class="timeline-content font-weight-mormal font-size-lg pl-3">
                    <p>
                        <strong>${getDateTime(actividad.fecha_creado)}:</strong>
                        ${remitentesHTML}
                    </p>
                    <div>
                        ${actividad.contenido}
                    </div>
                </div>
            `;
            fragment.appendChild(DIV);
        });
        contenedor.appendChild(fragment);
    }

    function getDateTime(fecha, getHora = false) {
        if (getHora) {
            return new Date(fecha).toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        return new Date(fecha).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
</script>
