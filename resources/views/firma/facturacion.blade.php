@extends('firma.layouts.app')

@section('titulo', 'Facturación')
@section('descripcion', 'Datos para la Facturación')
@section('imagen', '')


@section('contenido')
    @php
        $telefono = $user->telefono;
        if (str_starts_with($telefono, '0')) {
            $telefono = '593' . substr($telefono, 1, strlen($telefono));
        }
    @endphp
    <div class="content d-flex flex-column flex-column-fluid w-75 mx-auto">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="card card-custom">
                    <div class="card-header">
                        <h3 class="card-title">
                            Datos de Facturación
                        </h3>

                    </div>
                    <!--begin::Form-->
                    <form id="form-datos-facturacion">
                        <div class="card-body">
                            <div class="form-group">
                                <label>RUC <span class="text-danger">*</span></label>
                                <div id="spinner">
                                    <input type="text" class="form-control" name="inputRuc" id="inputRuc"
                                        oninput="if(this.value.length > 13) this.value = this.value.slice(0, 13);"
                                        autocomplete="off" placeholder="1711254789001">
                                    <span class="form-text text-danger d-none" id="helperTextRuc"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Empresa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Perseo" id="inputEmpresa" />
                                <span class="form-text text-danger d-none" id="helperTextEmpresa"></span>
                            </div>
                            <div class="form-group">
                                <label for="inputProductos">Producto<span class="text-danger">*</span></label>
                                <select class="form-control" id="inputProductos">
                                    <option value="" disabled selected>Seleccionar producto</option>
                                    <option value="Plan inicial: 6.49+iva 30 docs al año">Plan inicial: 6.49+iva 30 docs al
                                        año</option>
                                    <option value="Plan básico: 9.99+iva 100 docs al año ">Plan básico: 9.99+iva 100 docs al
                                        año</option>
                                    <option value="Plan Premium: 29.99+iva docs imilitados al año">Plan Premium: 29.99+iva
                                        docs imilitados al año</option>
                                    <option value="Firma electrónica 1 año $19">Firma electrónica 1 año $19</option>
                                    <option value="Firma electrónica 2 años $30">Firma electrónica 2 años $30
                                    </option>
                                    <option value="Firma electrónica 3 años $43">Firma electrónica 3 años $43
                                    </option>
                                    <option value="Firma electrónica 4 años $54">Firma electrónica 4 años $54
                                    </option>
                                    <option value="Firma electrónica 5 años $64">Firma electrónica 5 años $64
                                    </option>
                                </select>
                                <span class="form-text text-danger d-none" id="helperTextProcutos">Debe seleccionar un
                                    producto</span>
                            </div>
                            <div class="form-group">
                                <label>Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" placeholder="correo@dominio.com"
                                    id="inputCorreo" />
                                <span class="form-text text-danger" id="helperTextCorreo"> </span>
                            </div>
                            <div class="form-group">
                                <label>Teléfono <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" placeholder="0987654321" id="inputTelefono" />
                                <span class="form-text text-danger d-none" id="helperTextTelefono"></span>
                            </div>
                            <div class="form-group mb-1">
                                <label for="inputObservacion">Observación</span></label>
                                <textarea class="form-control" style="resize: none" id="inputObservacion" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" id="btnSendMessage" class="btn btn-success mr-2">Enviar datos por
                                Whatsapp</button>
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        const expresiones = {
            empresa: /^[a-zA-ZÀ-ÿÑñáéíóúÁÉÍÓÚ\s\.,-_ ]{1,50}$/, // solo letras y espacios
            correo: /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/, //formato correo
            telefono: /(\+|\+593)?([0-9]){10,20}/ // 10 a 20 numeros.
        }
        const campos = {
            ruc: false,
            empresa: false,
            producto: false,
            correo: false,
            telefono: false
        }

        window.addEventListener('load', () => {
            const formInputs = document.getElementById('form-datos-facturacion');
            recuperarInformacion();
            const listInputs = ['inputRuc', 'inputEmpresa', 'inputTelefono', 'inputCorreo'];
            const btnSend = document.getElementById('btnSendMessage');

            listInputs.forEach(inputName => {
                document.getElementById(inputName).addEventListener('blur', validarFormulario)
            })

            formInputs.addEventListener('submit', submitFormulario);
        })

        async function submitFormulario(e) {
            e.preventDefault();
            const formInputs = document.getElementById('form-datos-facturacion');
            const listInputs = ['inputRuc', 'inputEmpresa', 'inputTelefono', 'inputCorreo'];
            const btnSend = document.getElementById('btnSendMessage');
            btnSend.setAttribute('disabled', 'true');
            const inputProductos = formInputs.inputProductos;
            const helperTextPhone = document.getElementById('helperTextTelefono');
            const helperTextCorreo = document.getElementById('helperTextCorreo');

            if (inputProductos.value == "") {
                inputProductos.nextElementSibling.classList.remove('d-none')
            } else {
                campos.producto = true;
                inputProductos.nextElementSibling.classList.add('d-none')
            }

            const datasend = {
                _token: '{{ csrf_token() }}',
                correo: formInputs.inputCorreo.value,
                celular: formInputs.inputTelefono.value
            };

            const rest = await fetch("{{ route('admin.verificaremailcelular') }}", {
                method: 'POST',
                body: JSON.stringify(datasend),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            const [valor1, valor2] = await rest.json();

            if (valor1 == 1) {
                helperTextCorreo.classList.add('d-none');
                campos.correo = true;
            } else {

                helperTextCorreo.textContent = "El correo ingresado no es válido";
                helperTextCorreo.classList.remove('d-none')
                campos.correo = false;
            }

            if (valor2 == 1) {
                helperTextPhone.classList.add('d-none');
                campos.telefono = true;
            } else {
                helperTextPhone.textContent = "El número de teléfono ingresado no es válido";
                helperTextPhone.classList.remove('d-none')
                campos.telefono = false;
            }


            if (campos.ruc &&
                campos.empresa &&
                campos.producto &&
                campos.correo &&
                campos.telefono
            ) {
                const text =
                    `💻 *Solicitud de Factura Firma electrónica*🖊\n\n 📟 *RUC:* ${formInputs.inputRuc.value}\n 🏢 *Empresa:* ${formInputs.inputEmpresa.value}\n🏷 *Producto:* ${formInputs.inputProductos.value}\n📧 *Mail:* ${formInputs.inputCorreo.value}\n📱 *Teléfono:* ${formInputs.inputTelefono.value}\n📝 *Observación:* ${formInputs.inputObservacion.value}`
                const url =
                    `https://api.whatsapp.com/send?phone={{ $telefono }}&text=${encodeURI(text)}`

                window.open(url, '_blank');
                window.focus();
                location.href = "{{ route('inicio', Request::segment(2)) }}"

            } else {
                btnSend.removeAttribute('disabled')
                listInputs.forEach(inputName => {
                    const input = document.getElementById(inputName);
                    validarFormulario(input);
                })
            }
        }

        const validarFormulario = (e) => {
            const id = (e?.target?.id) ? e.target.id : e.id;
            const target = (e?.target) ? e.target : e;
            switch (id) {
                case "inputRuc":
                    validarCampo(expresiones.ruc, target, 'ruc',
                        'El Ruc solo admite valores numéricos con una longitud de 13 dígitos y termina en 001');
                    break;
                case "inputEmpresa":
                    validarCampo(expresiones.empresa, target, 'empresa', 'El nombre de la empresa es requerido');
                    break;
                case "inputCorreo":
                    validarCampo(expresiones.correo, target, 'correo',
                        'El correo no debe ser vacío y debe tener un formato válido');
                    break;
                case "inputTelefono":
                    validarCampo(expresiones.telefono, target, 'telefono',
                        'El número de teléfono es requerido y debe ser númerico');
                    break;
            }
        }

        const validarCampo = (expresion, input, campo, sms) => {
            const helperText = input.nextElementSibling;
            if (expresion.test(input.value)) {
                //helperText.classList.add('d-none')
                campos[campo] = true;
            } else {
                //helperText.classList.remove('d-none')
                helperText.textContent = sms;
                campos[campo] = false;
            }
        }

        async function recuperarInformacion() {
            const inputRuc = document.getElementById('inputRuc');
            const spiner = document.getElementById('spinner');
            const helperText = inputRuc.nextElementSibling;

            inputRuc.addEventListener('blur', function() {
                const text = this.value;
                if (text != "") {
                    var extraer = text.substr(10, 3);
                    if (text.length === 13 && extraer == "001") {
                        helperText.classList.add('d-none');
                        spiner.classList.add('spinner', 'spinner-success', 'spinner-right');
                        $.ajax({
                            url: "{{ route('firma.index') }}",
                            headers: {
                                'usuario': 'perseo',
                                'clave': 'Perseo1232*'
                            },
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                identificacion: text
                            },
                            success: function(data) {
                                spiner.classList.remove('spinner', 'spinner-success',
                                    'spinner-right');
                                if (data.identificacion) {
                                    $("#inputEmpresa").val(data.razon_social);
                                    $("#inputCorreo").val(data.correo);
                                    $("#inputTelefono").val(data.telefono2);
                                }
                            }
                        });
                    } else {
                        helperText.textContent = "Ingrese un Ruc válido"
                        helperText.classList.toggle('d-none');
                    }
                }
            })
        }
    </script>
@endsection
