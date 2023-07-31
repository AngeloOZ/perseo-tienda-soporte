<form action="{{ route('soporte.registrar_califcacion') }}" id="formCalificacion" method="POST">
    @csrf
    <div class="contenedor-pregunta">
        <h3 class="title-pregunta">¿Cómo calificaría la amabilidad y cordialidad del servicio de atención al cliente que recibió en su última interacción con nuestro equipo de soporte?</h3>
        <div class="radio-tile-group">
            <div class="input-container">
                <input id="" type="radio" value="1" name="pregunta_1">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-angry"></i>
                    <label for="">Nada satisfecho</label>
                </div>
            </div>

            <div class="input-container">
                <input id="" type="radio" value="2" name="pregunta_1">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-frown"></i>
                    <label for="">Poco satisfecho</label>
                </div>
            </div>

            <div class="input-container">
                <input id="" type="radio" value="3" name="pregunta_1">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-meh"></i>
                    <label for="">Normal</label>
                </div>
            </div>

            <div class="input-container">
                <input id="" type="radio" value="4" name="pregunta_1">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-smile-beam"></i>
                    <label for="">Satisfecho</label>
                </div>
            </div>
            <div class="input-container">
                <input id="" type="radio" value="5" name="pregunta_1">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-laugh-beam"></i>
                    <label for="">Muy satisfecho</label>
                </div>
            </div>
        </div>
    </div>

    <div class="contenedor-pregunta">
        <h3 class="title-pregunta">¿Qué tan satisfecho está con la solución que recibió para su problema en su última interacción con nuestro equipo de soporte?</h3>
        <div class="radio-tile-group">
            <div class="input-container">
                <input id="" type="radio" value="1" name="pregunta_2">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-angry"></i>
                    <label for="">Muy lento</label>
                </div>
            </div>

            <div class="input-container">
                <input id="" type="radio" value="2" name="pregunta_2">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-frown"></i>
                    <label for="">Lento</label>
                </div>
            </div>

            <div class="input-container">
                <input id="" type="radio" value="3" name="pregunta_2">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-meh"></i>
                    <label for="">Un poco lento</label>
                </div>
            </div>

            <div class="input-container">
                <input id="" type="radio" value="4" name="pregunta_2">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-smile-beam"></i>
                    <label for="">Rápido</label>
                </div>
            </div>
            <div class="input-container">
                <input id="" type="radio" value="5" name="pregunta_2">
                <div class="radio-tile">
                    <i class="fa-solid fa-face-laugh-beam"></i>
                    <label for="">Muy rápido</label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-5 d-none" id="ctnComentario">
        <label for="" class="font-size-h3 font-weight-bold">Lamentamos que el servicio
            no
            haya sido de su agrado</label>
        <textarea name="comentario" class="form-control" id="idComentario" style="resize: none" rows="3"></textarea>
        <span class="text-muted">Expliquenos que sucedio para mejorar nuestro servicio</span>
    </div>

    <input type="hidden" name="ticketid" value="{{ $ticket->ticketid }}">
    <div class="text-center mt-5 mb-10">
        <button type="submit" id="btnSendScore" class="btn btn-primary">Enviar calificación</button>
    </div>
</form>
