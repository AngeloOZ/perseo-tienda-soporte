<div class="tab-content" id="contenedor_productos">
    <div class="tab-pane fade show active" id="contafacil" role="tabpanel" aria-labelledby="contafacil-tab">
        <div class="row">
            @foreach ($productos['contafacil'] as $producto)
                @php
                    $imagen = asset('assets/media/default.jpg');
                    if (file_exists(public_path('shop/webp/' . $producto->productosid . '.webp'))) {
                        $imagen = asset('shop/webp/' . $producto->productosid . '.webp');
                    }
                @endphp
                <div class="col-md-6 col-lg-4 col-xxl-4">
                    <div class="card card-custom gutter-b card-stretch">
                        <div class="card-body d-flex flex-column rounded bg-light justify-content-between">
                            <div class="text-center rounded mb-7">
                                <img src="{{ $imagen }}" loading="lazy" class="mw-100 w-200px" />
                            </div>
                            <div>
                                <h4 class="font-size-h5 text-dark-75 font-weight-bolder">
                                    {{ $producto->descripcion }}
                                </h4>
                                <div class="font-size-h6 text-muted font-weight-bolder">
                                    @if ($producto->descuento != 0)
                                        <span class="text-danger mr-1">-{{ $producto->descuento }}%</span>
                                        <span class="text-dark-75">${{ number_format($producto->precio, 2) }} no
                                            incluye IVA</span>
                                        <p style="font-size: 14px; font-weight: 400">Precio recomendado <span
                                                style="text-decoration: line-through">${{ number_format($producto->preciobase, 2) }}</span>
                                        </p>
                                    @else
                                        ${{ number_format($producto->precio, 2) }} no
                                        incluye IVA
                                    @endif
                                </div>
                                @if ($producto->contenido != null)
                                    <div class="mt-1">
                                        <button class="btn btn-info btn-sm btn-info-product"
                                            data-content-html='{{ $producto->contenido }}'>Más
                                            información</button>
                                    </div>
                                @endif
                                <div class="button-contenedor mt-3" data-producto='{!! json_encode($producto) !!}'>
                                    <p class="bg-success indicador_cantidad d-none">
                                        0</p>
                                    <button type="button" data-action="remover" class="btn btn-dark">-</button>
                                    <button type="button" data-action="agregar" class="btn btn-dark ml-2">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @foreach ($productos as $key => $categoria)
        @php
            if ($key == 'contafacil') continue;
        @endphp
        <div class="tab-pane fade" id="{{ $key }}" role="tabpanel" aria-labelledby="{{ $key }}-tab-5">
            <div class="row">
                @foreach ($categoria as $producto)
                    @php
                        $imagen = asset('assets/media/default.jpg');
                        if (file_exists(public_path('shop/webp/' . $producto->productosid . '.webp'))) {
                            $imagen = asset('shop/webp/' . $producto->productosid . '.webp');
                        }
                    @endphp
                    <div class="col-md-6 col-lg-4 col-xxl-4">
                        <div class="card card-custom gutter-b card-stretch">
                            <div class="card-body d-flex flex-column rounded bg-light justify-content-between">
                                <div class="text-center rounded mb-7">
                                    <img src="{{ $imagen }}" loading="lazy" class="mw-100 w-200px" />
                                </div>
                                <div>
                                    <h4 class="font-size-h5 text-dark-75 font-weight-bolder">
                                        {{ $producto->descripcion }}
                                    </h4>
                                    <div class="font-size-h6 text-muted font-weight-bolder">
                                        @if ($producto->descuento != 0)
                                            <span class="text-danger mr-1">-{{ $producto->descuento }}%</span>
                                            <span class="text-dark-75">${{ number_format($producto->precio, 2) }} no
                                                incluye IVA</span>
                                            <p style="font-size: 14px; font-weight: 400">Precio recomendado <span
                                                    style="text-decoration: line-through">${{ number_format($producto->preciobase, 2) }}</span>
                                            </p>
                                        @else
                                            ${{ number_format($producto->precio, 2) }} no
                                            incluye IVA
                                        @endif
                                    </div>
                                    @if ($producto->contenido != null)
                                        <div class="mt-1">
                                            <button class="btn btn-info btn-sm btn-info-product"
                                                data-content-html='{{ $producto->contenido }}'>Más
                                                información</button>
                                        </div>
                                    @endif
                                    <div class="button-contenedor mt-3" data-producto='{!! json_encode($producto) !!}'>
                                        <p class="bg-success indicador_cantidad d-none">
                                            0</p>
                                        <button type="button" data-action="remover" class="btn btn-dark">-</button>
                                        <button type="button" data-action="agregar"
                                            class="btn btn-dark ml-2">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
