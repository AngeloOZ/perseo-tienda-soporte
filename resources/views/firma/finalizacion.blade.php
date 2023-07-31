@extends('firma.layouts.app')


@section('contenido')
    <div class="content d-flex flex-column flex-column-fluid w-75 mx-auto" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid">
                <div class="wizard wizard-4" id="kt_wizard" data-wizard-state="step-first" data-wizard-clickable="true">
                    <!--begin::Wizard Nav-->
                    <div class="wizard-nav">
                        <div class="wizard-steps">
                            <div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
                                <div class="wizard-wrapper">
                                    <div class="wizard-number">1</div>
                                    <div class="wizard-label">
                                        <div class="wizard-title">Datos</div>
                                        <div class="wizard-desc">Ingrese sus datos</div>
                                    </div>
                                </div>
                            </div>
                            <div class="wizard-step" data-wizard-type="step">
                                <div class="wizard-wrapper">
                                    <div class="wizard-number">2</div>
                                    <div class="wizard-label">
                                        <div class="wizard-title">Imágenes</div>
                                    <div class="wizard-desc">Adjunte las imágenes</div>
                                    </div>
                                </div>
                            </div>
                            <div class="wizard-step" data-wizard-type="step">
                                <div class="wizard-wrapper">
                                    <div class="wizard-number">3</div>
                                    <div class="wizard-label">
                                        <div class="wizard-title">Documentos</div>
                                        <div class="wizard-desc">Adjunte Documentos</div>
                                    </div>
                                </div>
                            </div>
                            <div class="wizard-step" data-wizard-type="step">
                                <div class="wizard-wrapper">
                                    <div class="wizard-number">4</div>
                                    <div class="wizard-label">
                                        <div class="wizard-title">Finalización</div>
                                        <div class="wizard-desc">Estado del Proceso</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card card-custom card-shadowless rounded-top-0">
                        <!--begin::Body-->
                        <div class="card-body p-0">
                            <div class="row justify-content-center py-8 px-8 py-lg-15 px-lg-10">
                                <div class="col-xl-12 col-xxl-10">
                                    <!--begin::Wizard Form-->

                                    <div class="row justify-content-center">


                                        <div class="col-xl-9">
                                            <!--begin::Wizard Step 1-->
                                            <div class="my-5 step" data-wizard-type="step-content"
                                                data-wizard-state="current">

                                                <div class="form-group row">

                                                </div>
                                            </div>

                                            <div class="my-5 step" data-wizard-type="step-content">

                                                <div class="form-group row">
                                                </div>

                                            </div>

                                            <div class="my-5 step" data-wizard-type="step-content">

                                                <div class="form-group row">

                                                </div>

                                            </div>
                                            <div class="my-5 step" data-wizard-type="step-content">

                                                <div class="form-group row">
                                                    <div class="col-xl-12">
                                                        <div class="card  gutter-b" style="height: 200px;">
                                                            <!--begin::Body-->
                                                            <div class="card-body d-flex flex-column">
                                                                <div
                                                                    class="d-flex align-items-center justify-content-between flex-grow-1">
                                                                    <div class=" mx-auto">
                                                                        @if ($verificacion == 1)
                                                                            <h5 class="font-weight-bolder correcto">La
                                                                                información
                                                                                se ha compleado satisfactoriamente</h5>
                                                                        @else
                                                                            <h5 class="font-weight-bolder incorrecto">La
                                                                                información
                                                                                no se ha compleado satisfactoriamente
                                                                            </h5>
                                                                            <small>Por favor vuelva a intentarlo</small>
                                                                        @endif
                                                                        <div class="text-muted font-size-lg mt-2">
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class=" pt-0 pt-md-5">
                                                                    <div class="text-center ">
                                                                        @if ($verificacion == 1)
                                                                            <li
                                                                                class="far fa-check-circle text-success icon-7x ">
                                                                            </li>
                                                                        @else
                                                                            <li
                                                                                class="far fa-times-circle text-danger icon-7x ">

                                                                            </li>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!--end::Body-->
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            
                                        </div>
                                    </div>

                                    <!--end::Wizard Form-->
                                </div>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            kizzard.init();
        });


        var kizzard = function() {

            var _wizardEl;
            var _wizardObj;

            var _initWizard = function() {

                _wizardObj = new KTWizard(_wizardEl, {
                    startStep: 4,
                    clickableSteps: false,
                    navigation: false
                });

            }
            return {
                init: function() {
                    _wizardEl = KTUtil.getById('kt_wizard');
                    _initWizard();
                }
            };
        }();
    </script>
@endsection
