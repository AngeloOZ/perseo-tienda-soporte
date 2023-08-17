@extends('pagos.layouts.app')
@section('titulo', 'Registrar pago')
@section('descripcion', 'Productos listos para la compra')
{{-- @section('imagen', asset('assets/media/firmas.jpg')) --}}

@section('contenido')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        ::selection {
            color: #fff;
            background: #181C32;
        }

        .file-container {
            width: 420px;
            max-width: 100%;
            background: #fff;
            border-radius: 5px;
            padding: 40px;
            border: 2px solid #181C32;
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
            margin: 30px 0;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 5px;
            border: 2px dashed #181C32;
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
    </style>
    <div class="content d-flex flex-column flex-column-fluid w-100 mx-auto ">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="card card-custom mb-8">
                    <div class="card-body ">
                        <div class="d-flex justify-content-center flex-column flex-md-row">
                            <div class="file-container m-auto m-md-0">
                                <header>Subir comprobantes de factura No: {{ $renovacion->uuid }}</header>
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
                const numberFiles = formData.getAll(nameKey).length;
                if (numberFiles == 0) return;
                formData.append('uuid', "{{ $renovacion->uuid }}");
                formData.append('renovacionid', "{{ $renovacion->renovacionid }}");

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
                    showAlert(
                        "Hubo un error",
                        "No se pudo registrar el pago, por favor inténtalo más tarde",
                        "error"
                    );
                }
            })

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

        function showAlert(title, message, icon = "warning") {
            Swal.fire({
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
