@extends('pagos.layouts.app')
@section('titulo', 'Registrar pago')
@section('descripcion', 'Productos listos para la compra')

@php
    $bancoOrigen = ['Banco Pichincha', 'Banco del Pacifíco', 'Banco Guayaquil', 'Banco Internacional', 'Banco Bolivariano', 'Banco de Loja', 'Banco de Machala', 'Coperativa JEP', 'Coperativa 29 de Octubre', 'OTRO'];
    
    $bancoDestino = ['Banco Pichincha', 'Banco Produbanco'];
@endphp

@section('contenido')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        ::selection {
            color: #fff;
            background: #181C32;
        }

        .form-input-list {
            width: 420px;
            max-width: 100%;
        }

        .file-container {
            width: 420px;
            max-width: 100%;
            background: #fff;
            border-radius: 5px;
            border: 0px solid #181C32;
        }

        .file-container header {
            color: #181C32;
            font-size: 25px;
            font-weight: 600;
            text-align: center;
        }

        .file-container form {
            height: 167px;
            display: flex;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 5px;
            border: 2px dashed #181C32;
            margin: 0;
        }

        form :where(i, p) {
            color: #181C32;
        }

        form i {
            font-size: 50px;
        }

        form p {
            margin-top: 15px;
            font-size: 16px;
        }

        section .file-row {
            margin-bottom: 10px;
            background: #E9F0FF;
            list-style: none;
            padding: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        section .file-row i {
            color: #181C32;
            font-size: 30px;
        }

        section .details span {
            font-size: 14px;
            margin-right: 8px;
        }

        .uploaded-area {
            margin: auto;
            max-height: 390px;
            overflow-y: scroll;
        }

        .uploaded-area .file-row .content-up {
            display: flex;
            align-items: center;
        }

        .uploaded-area .file-row .details {
            display: flex;
            margin-left: 15px;
            flex-direction: column;
        }

        .uploaded-area .file-row .details .size {
            color: #404040;
            font-size: 13px;
            font-weight: bold;
        }

        .uploaded-area i.hover {
            font-size: 25px;
            cursor: pointer;
        }

        .preview {
            object-fit: cover;
            max-height: 60px;
            max-width: 60px;
        }

        @media screen and (max-width: 450px) {
            .file-container {
                padding: 20px;
            }

            .file-container header {
                font-size: 18px;
                font-weight: 600;
                text-align: center;
            }

            .file-container form {
                height: 130px;
                margin: 20px 0;
                margin-bottom: 0;
            }

            form i {
                font-size: 40px;
            }

            form p {
                margin-top: 15px;
                font-size: 14px;
                text-align: center
            }

            section .details span {
                font-size: 14px;
                text-overflow: ellipsis
            }

            .preview {
                max-height: 40px;
                max-width: 40px;
            }
        }

        @media screen and (max-width: 350px) {

            .file-container header {
                font-size: 16px;
            }

            .file-container form {
                height: 100px;
                margin: 15px 0;
            }

            form i {
                font-size: 30px;
                margin-top: 10px;
            }

            form p {
                margin-top: 10px;
                font-size: 12px;
            }

            section .details span {
                font-size: 10px;
            }

            .uploaded-area .file-row .details .size {
                font-size: 8px;
            }

            .uploaded-area i.hover {
                font-size: 20px;
            }

            .preview {
                max-height: 30px;
                max-width: 30px;
            }
        }

        .overlay {
            background-color: rgba(0, 0, 0, 0.7);
            width: 100%;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;

            display: grid;
            place-content: center;
        }

        .loader-upload {
            width: 175px;
            height: 80px;
            display: block;
            margin: auto;
            background-image: radial-gradient(circle 25px at 25px 25px, #FFF 100%, transparent 0), radial-gradient(circle 50px at 50px 50px, #FFF 100%, transparent 0), radial-gradient(circle 25px at 25px 25px, #FFF 100%, transparent 0), linear-gradient(#FFF 50px, transparent 0);
            background-size: 50px 50px, 100px 76px, 50px 50px, 120px 40px;
            background-position: 0px 30px, 37px 0px, 122px 30px, 25px 40px;
            background-repeat: no-repeat;
            position: relative;
            box-sizing: border-box;

            transform: scale(1.8);
        }

        .loader-upload::after {
            content: '';
            left: 50%;
            bottom: 30px;
            transform: translate(-50%, 0);
            position: absolute;
            border: 15px solid transparent;
            border-bottom-color: #181C32;
            box-sizing: border-box;
            animation: fadePull 1s linear infinite;
        }

        .loader-upload::before {
            content: '';
            left: 50%;
            bottom: 15px;
            transform: translate(-50%, 0);
            position: absolute;
            width: 15px;
            height: 15px;
            background: #181C32;
            box-sizing: border-box;
            animation: fadePull 1s linear infinite;
        }

        @keyframes fadePull {
            0% {
                transform: translate(-50%, 15px);
                opacity: 0;
            }

            50% {
                transform: translate(-50%, 0px);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -15px);
                opacity: 0;
            }
        }
    </style>
    <div class="content d-flex flex-column flex-column-fluid w-100 mx-auto ">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="card card-custom mb-8">
                    <div class="card-header" style="min-height: 50px">
                        <h3 class="card-title">
                            Comprobante de factura No: {{ $renovacion->uuid }}
                        </h3>
                    </div>
                    <div class="card-body p-0 py-6">
                        <div class="d-flex justify-content-center flex-column flex-md-row">
                            <div class="file-container m-auto m-md-0">
                                <div>
                                    <div class="form-group m-0 mb-3">
                                        <label>Valor de la factura</label>
                                        <input value="{{ $total }}" disabled class="form-control form-control-sm" />
                                    </div>
                                    <div class="form-group m-0 mb-3">
                                        <label>Número de comprobante<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" placeholder="XXXXXXXXXX"
                                            value="{{ $renovacion->numero_comprobante }}" name="numero_comprobante"
                                            id="numero_comprobante" />
                                    </div>
                                    <div class="form-group m-0 mb-3">
                                        <label>Banco de Origén<span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" name="banco_origen" id="banco_origen">
                                            @foreach ($bancoOrigen as $banco)
                                                <option>{{ $banco }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group m-0 mb-5">
                                        <label>Banco de Destino<span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm" name="banco_destino"
                                            id="banco_destino">
                                            @foreach ($bancoDestino as $banco)
                                                <option>{{ $banco }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <form action="#">
                                    <input class="file-input" type="file" name="file" hidden
                                        accept="image/jpg', image/jpeg, image/png" multiple>
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Buscar archivo para cargar</p>
                                </form>
                            </div>
                            <div class="ml-0 ml-md-4">
                                <section class="progress-area"></section>
                                <section class="uploaded-area mt-4 my-md-0"></section>
                                <button type="button" id="btnUpload" class="btn btn-primary w-100 d-none">Subir
                                    comprobantes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection
@section('modal')
    <div class="overlay d-none" id="overlay-loader-upload">
        <span class="loader-upload"></span>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/uuid@8.3.2/dist/umd/uuid.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', _ => {
            let formData = new FormData();
            const nameKey = 'comprobantes[]'
            const maxNumberFiles = 5;
            const form = document.querySelector("form");
            const fileInput = document.querySelector(".file-input");
            const uploadedArea = document.querySelector(".uploaded-area");
            const btnUpload = document.getElementById('btnUpload');

            const comprobante = document.getElementById('numero_comprobante');
            const bancoOrigen = document.getElementById('banco_origen');
            const bancoDestino = document.getElementById('banco_destino');

            @if ($renovacion->cobrosid != null)
                $('#numero_comprobante').val("{{ $renovacion->numero_comprobante }}");
                $('#banco_origen').val("{{ $renovacion->banco_origen }}");
                $('#banco_destino').val("{{ $renovacion->banco_destino }}");
            @endif

            form.addEventListener("click", () => {
                const numberFiles = formData.getAll(nameKey).length;

                if (numberFiles >= maxNumberFiles) {
                    showAlert("Número de archivos execedidos",
                        `Estas tratando de cargar demasiados archivos, la cantidad máxima es de ${maxNumberFiles}`
                    );
                    return;
                }
                fileInput.click();
            });

            fileInput.addEventListener('change', function(e) {
                const files = e.target.files;
                const numberFilesData = formData.getAll(nameKey).length;
                const numberAllFiles = numberFilesData + files.length;

                if (numberAllFiles > maxNumberFiles) {
                    showAlert("Número de archivos execedidos",
                        `Estas tratando de cargar demasiados archivos, la cantidad máxima es de ${maxNumberFiles}`
                    );
                    return;
                }

                files.forEach((file) => {
                    if (validateFileType(file)) {
                        file.uuid = uuid.v4();
                        formData.append(nameKey, file);
                        showSelectedFiles(file);
                    }
                });
                showButtonUpload();
            });

            uploadedArea.addEventListener('click', e => {
                if (e.target.classList.contains('hover')) {
                    fileInput.value = "";
                    const parent = e.target.parentElement;
                    const uuid = parent.dataset.uuidFile;
                    const newFormData = new FormData();

                    formData.getAll(nameKey).forEach((file) => {
                        if (file.uuid !== uuid) {
                            newFormData.append(nameKey, file);
                        }
                    });
                    formData = newFormData;
                    parent.remove();
                    showButtonUpload();
                }
            })

            btnUpload.addEventListener('click', async (e) => {
                if (comprobante.value.length <= 4) {
                    showAlert("Comprobante inválido",
                        `El comprobante debe tener al menos 5 dígitos`
                    );
                    return;
                }

                $('#overlay-loader-upload').removeClass('d-none');
                const numberFiles = formData.getAll(nameKey).length;
                if (numberFiles == 0) return;
                formData.append('uuid', "{{ $renovacion->uuid }}");
                formData.append('renovacionid', "{{ $renovacion->renovacionid }}");
                formData.append('numero_comprobante', comprobante.value);
                formData.append('banco_origen', bancoOrigen.value);
                formData.append('banco_destino', bancoDestino.value);

                try {
                    const config = {
                        method: "POST",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: formData,
                    }

                    const url =
                        "{{ $renovacion->cobrosid ? route('pagos.actualizar') : route('pagos.guardar') }}";
                    const response = await fetch(url, config);
                    if (!response.ok) throw new Error(`Request failed with status ${response.status}`);
                    const data = await response.json();
                    window.location.reload()
                } catch (error) {
                    console.log(error);
                    $('#overlay-loader-upload').addClass('d-none');
                    showAlert(
                        "Hubo un error",
                        "No se pudo registrar el pago, por favor inténtalo más tarde",
                        "error"
                    );
                }
            })

            comprobante.addEventListener('input', e => {
                e.target.value = e.target.value.replace(/[^0-9]/g, "");
            });

            function showSelectedFiles(file) {
                const uploadedHTML = `<li class="file-row" data-uuid-file="${file.uuid}">
                    <div class="content-up upload">
                        <div class="image-preview">
                            <img src="#" alt="preview ${file.name}" class="img-thumbnail preview d-none">
                        </div>
                        <div class="details">
                        <span class="name">${file.name}</span>
                        <span class="size">${formatFileSize(file.size)}</span>
                        </div>
                    </div>
                    <i class="fas fa-trash-alt hover"></i>
                    </li>`;
                uploadedArea.classList.remove("onprogress");
                uploadedArea.insertAdjacentHTML("afterbegin", uploadedHTML);

                const imagePreview = uploadedArea.querySelector(".image-preview img");
                const reader = new FileReader();

                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }

            function showButtonUpload() {
                const numberFiles = formData.getAll(nameKey).length;
                if (numberFiles > 0) {
                    btnUpload.classList.remove('d-none');
                } else {
                    btnUpload.classList.add('d-none');
                }
            }
        });

        function validateFileType(file, options = {}) {
            const {
                validExtensions = ['jpg', 'jpeg', 'png'], maxSize = 2097152
            } = options;

            const extension = file.name.split('.').pop();

            if (!validExtensions.includes(extension)) {
                showAlert("Tipo de archivo no válido",
                    `El archivo <strong>${file.name}</strong> no es una imagén permitida, solo se admite archivos de tipo: <span class='text-danger'>${validExtensions.join(', ')}</span>`
                );
                return false;
            }

            if (file.size > maxSize) {
                showAlert("Tamaño de archivo excedido",
                    `El archivo <strong>${file.name}</strong> es demasiado grande. El tamaño máximo permitido es ${formatFileSize(maxSize)}.`
                );
                return false;
            }

            return true;
        }

        async function showAlert(title, message, icon = "warning") {
            await Swal.fire({
                title: title,
                html: message,
                icon: icon,
                confirmButtonText: "OK",
            });
        }

        function formatFileSize(fileTotal) {
            let fileSize;

            if (fileTotal < 1024) {
                fileSize = fileTotal + " KB";
            } else {
                fileSize = (fileTotal / (1024 * 1024)).toFixed(2) + " MB";
            }
            return fileSize;
        }
    </script>
@endsection
