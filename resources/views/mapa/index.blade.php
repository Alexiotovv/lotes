@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('css/toastr.min.css')}}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css"/>
@endsection

@section('content')
    <h3 class="mb-3">Mapa de Lotes</h3>

    <div id="map" style="height: 650px; border-radius: 10px;"></div>

    <form id="loteForm" class="mt-4">
        @csrf
        <input type="hidden" name="lote_id" id="lote_id">
        <input type="hidden" name="coordenadas" id="coordenadas">

        <div class="row">
            <div class="col-md-2">
                <label class="form-label">Código</label>
                <input type="text" name="codigo" id="codigo" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Área (m²)</label>
                <input type="number" name="area_m2" id="area_m2" class="form-control" step="0.01">
            </div>
            <div class="col-md-2">
                <label class="form-label">Precio m²</label>
                <input type="number" name="precio_m2" id="precio_m2" class="form-control" step="0.01">
            </div>
        

        {{-- 🧱 Campos de medidas del lote --}}
        
            <div class="col-md-2">
                <label class="form-label">Frente (m)</label>
                <input type="number" name="frente" id="frente" class="form-control" step="0.01">
            </div>
            <div class="col-md-2">
                <label class="form-label">Lado Izquierdo (m)</label>
                <input type="number" name="lado_izquierdo" id="lado_izquierdo" class="form-control" step="0.01">
            </div>
            <div class="col-md-2">
                <label class="form-label">Lado Derecho (m)</label>
                <input type="number" name="lado_derecho" id="lado_derecho" class="form-control" step="0.01">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fondo (m)</label>
                <input type="number" name="fondo" id="fondo" class="form-control" step="0.01">
            </div>
        
            <div class="col-md-2">
                <label class="form-label">Latitud</label>
                <input type="text" name="latitud" id="latitud" class="form-control" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Longitud</label>
                <input type="text" name="longitud" id="longitud" class="form-control" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado_lote_id" id="estado_lote_id" class="form-select" required>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}">{{ $estado->estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                {{-- <div class="text-end"> --}}
                    <br>
                    <button type="button" id="guardarBtn" class="btn btn-outline-success">Guardar lote</button>
                    <button type="button" id="cancelarBtn" class="btn btn-outline-danger d-none">Cancelar</button>
                {{-- </div> --}}
            </div>
        </div>

    </form>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>

    <script>
        $(document).ready(function() {
            const mapConfig = @json($mapConfig ?? null);

            // ✅ Valores por defecto en caso de que no haya configuración
            const latInicial = mapConfig?.lat_map ? parseFloat(mapConfig.lat_map) : -3.844051;
            const lonInicial = mapConfig?.lon_map ? parseFloat(mapConfig.lon_map) : -73.3432986;
            const zoomInicial = mapConfig?.actual_zoom_map ? parseInt(mapConfig.actual_zoom_map) : 19;
            const maxZoom = mapConfig?.max_zoom_map ? parseInt(mapConfig.max_zoom_map) : 19;
            const minZoom = mapConfig?.min_zoom_map ? parseInt(mapConfig.min_zoom_map) : 15;
            console.log('🔧 Configuración del mapa cargada:', {
                latInicial, lonInicial, zoomInicial, maxZoom, minZoom
            });

            // ✅ Inicializar mapa con valores de la base de datos
            const map = L.map('map', {
                maxZoom: maxZoom,
                minZoom: minZoom
            }).setView([latInicial, lonInicial], zoomInicial);

            var calle = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: maxZoom,
                minZoom: minZoom,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);


            const imagenesSuperpuestas = @json($imagenesSuperpuestas ?? []);
            function cargarImagenesSuperpuestas() {
                if (!imagenesSuperpuestas || imagenesSuperpuestas.length === 0) {
                    console.log('No hay imágenes superpuestas para cargar');
                    return;
                }

                console.log('Cargando', imagenesSuperpuestas.length, 'imágenes superpuestas...');
                
                imagenesSuperpuestas.forEach(imgData => {
                    try {
                        const anchoLat = parseFloat(imgData.ancho_lat) || 0.0100;
                        const anchoLng = parseFloat(imgData.ancho_lng) || 0.0120;
                        const escala = parseFloat(imgData.escala) || 1.0;
                        const latCentro = parseFloat(imgData.lat_centro);
                        const lngCentro = parseFloat(imgData.lng_centro);
                        const opacidad = parseFloat(imgData.opacidad) || 0.85;
                        
                        const halfLat = (anchoLat * escala) / 2;
                        const halfLng = (anchoLng * escala) / 2;
                        
                        if (isNaN(latCentro) || isNaN(lngCentro) || isNaN(halfLat) || isNaN(halfLng)) {
                            console.error('Datos inválidos para imagen ID:', imgData.id);
                            return;
                        }

                        const bounds = [
                            [latCentro - halfLat, lngCentro - halfLng],
                            [latCentro + halfLat, lngCentro + halfLng]
                        ];

                        if (bounds.some(coord => coord.some(isNaN))) {
                            console.error('Bounds inválidos para imagen ID:', imgData.id, bounds);
                            return;
                        }

                        // Cargar la imagen superpuesta
                        L.imageOverlay(imgData.url_completa, bounds, {
                            opacity: opacidad,
                            interactive: false // Solo visual, no editable
                        }).addTo(map);
                        
                        console.log('✅ Imagen superpuesta cargada ID:', imgData.id);
                        
                    } catch (error) {
                        console.error('Error cargando imagen superpuesta ID:', imgData.id, error);
                    }
                });
                
                console.log('✅ Todas las imágenes superpuestas cargadas');
            }

            // Llamar cuando el mapa esté listo
            map.whenReady(() => {
                setTimeout(() => {
                    cargarImagenesSuperpuestas();
                }, 1000);
            });

            const drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            // 🧭 Cargar lotes
            function cargarLotes() {
                $.getJSON('{{ route("api.lotes.index") }}', function(lotes) {
                    drawnItems.clearLayers();

                    lotes.forEach(lote => {
                        let color = '#3388ff';
                        switch (lote.estado) {
                            case 'disponible': color = 'green'; break;
                            case 'reservado': color = 'orange'; break;
                            case 'vendido': color = 'red'; break;
                            case 'bloqueado': color = 'gray'; break;
                        }

                        if (lote.coordenadas) {
                            try {
                                let geom = JSON.parse(lote.coordenadas);
                                let layer = null;

                                if (geom.type === "Polygon") {
                                    layer = L.polygon(geom.coordinates[0].map(coord => [coord[1], coord[0]]), {
                                        color: lote.estado_lote.color,
                                        fillOpacity: 0.4,
                                    });
                                }

                                if (layer) {
                                    layer.addTo(drawnItems);
                                    const centro = layer.getBounds().getCenter();
                                    lote.latitud = lote.latitud ?? centro.lat;
                                    lote.longitud = lote.longitud ?? centro.lng;
                                }
                            } catch (e) {
                                console.error("Error parsing coordenadas:", e);
                            }
                        }

                        if (lote.latitud && lote.longitud) {

                            // Asigna color según el estado del lote
                            let iconUrl = '';

                            switch (lote.estado_lote?.estado?.toLowerCase()) {
                                case 'disponible':
                                    iconUrl = '/img/markers/marker-icon-green.png';
                                    break;
                                case 'reservado':
                                    iconUrl = '/img/markers/marker-icon-yellow.png';
                                    break;
                                case 'vendido':
                                    iconUrl = '/img/markers/marker-icon-orange.png';
                                    break;
                                case 'bloqueado':
                                    iconUrl = '/img/markers/marker-icon-red.png';
                                    break;
                                default:
                                    iconUrl = '/img/markers/marker-icon-blue.png';
                            }

                            const customIcon = L.icon({
                                iconUrl: iconUrl,
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                iconSize: [25, 41],
                                iconAnchor: [12, 41],
                                popupAnchor: [1, -34],
                                shadowSize: [41, 41]
                            });

                            // ✅ CREAR MARCADOR ARRASTRABLE (solo para edición visual)
                            const marker = L.marker([lote.latitud, lote.longitud], { 
                                icon: customIcon,
                                draggable: false  // Inicialmente no arrastrable
                            }).addTo(drawnItems);

                            // ✅ GUARDAR ID DEL LOTE EN EL MARCADOR
                            marker._loteId = lote.id;

                            marker.bindPopup(`
                                <b>${lote.codigo}</b><br>${lote.nombre || ''}<br>
                                <small>${lote.estado_lote.estado}</small><br>
                                <button class='btn btn-sm btn-primary editarLote' data-id='${lote.id}'>Editar</button>
                                <button class='btn btn-sm btn-danger eliminarLote' data-id='${lote.id}'>Eliminar</button>
                            `);
                        }



                    });
                });
            }

            cargarLotes();

            // Control de dibujo
            const drawControl = new L.Control.Draw({
                draw: {
                    polyline: false,
                    circle: false,
                    circlemarker: false,
                    rectangle: false,
                    marker: true,
                    polygon: true
                },
                edit: { featureGroup: drawnItems, remove: true }
            });
            map.addControl(drawControl);

            let currentLayer = null;

            map.on(L.Draw.Event.CREATED, function (event) {
                const layer = event.layer;
                if (currentLayer) drawnItems.removeLayer(currentLayer);
                drawnItems.addLayer(layer);
                currentLayer = layer;

                if (layer instanceof L.Polygon) {
                    const geojson = layer.toGeoJSON();
                    $('#coordenadas').val(JSON.stringify(geojson.geometry));
                    const centro = layer.getBounds().getCenter();
                    $('#latitud').val(centro.lat.toFixed(8));
                    $('#longitud').val(centro.lng.toFixed(8));
                }
                if (layer instanceof L.Marker) {
                    const latlng = layer.getLatLng();
                    $('#latitud').val(latlng.lat.toFixed(8));
                    $('#longitud').val(latlng.lng.toFixed(8));
                    $('#coordenadas').val('');
                }
            });

            // Guardar o actualizar
            $('#guardarBtn').on('click', function() {
                let id = $('#lote_id').val();
                if (!id) guardarLote();
                else actualizarLote(id);
            });

            function guardarLote() {
                let formData = new FormData($('#loteForm')[0]);
                $.ajax({
                    url: '{{ route("lotes.store") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $('#loteForm')[0].reset();
                            cargarLotes();
                        } else toastr.error(res.message);
                    },
                    error: () => toastr.error('❌ Error al guardar el lote'),
                });
            }

            function actualizarLote(id) {
                const data = {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    codigo: $('#codigo').val(),
                    nombre: $('#nombre').val(),
                    area_m2: $('#area_m2').val(),
                    precio_m2: $('#precio_m2').val(),
                    latitud: $('#latitud').val(),
                    longitud: $('#longitud').val(),
                    estado_lote_id: $('#estado_lote_id').val(),
                    frente: $('#frente').val(),
                    lado_izquierdo: $('#lado_izquierdo').val(),
                    lado_derecho: $('#lado_derecho').val(),
                    fondo: $('#fondo').val(),
                    coordenadas: $('#coordenadas').val()
                };
                
                console.log('📤 Actualizando lote:', data);
                
                $.ajax({
                    url: '/lotes/' + id,
                    method: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            resetForm();
                            cargarLotes();
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ Error completo:', xhr);
                        
                        if (xhr.status === 422) {
                            const response = xhr.responseJSON;
                            const errors = response.errors;
                            
                            // ✅ Mostrar información de debug si está disponible
                            if (response.debug) {
                                console.log('🐛 Debug info:', response.debug);
                                if (response.debug.same_codigo) {
                                    toastr.error('ERROR: Estás intentando actualizar con el mismo código que ya tiene el lote');
                                }
                            }
                            
                            for (const [field, errorMessages] of Object.entries(errors)) {
                                errorMessages.forEach(msg => toastr.error(`${field}: ${msg}`));
                            }
                        } else {
                            toastr.error('❌ Error al actualizar el lote');
                        }
                    }
                });
            }

            function resetForm() {
                $('#loteForm')[0].reset();
                $('#lote_id').val('');
                $('#cancelarBtn').addClass('d-none');
                
                // Desactivar arrastre
                drawnItems.eachLayer(function(layer) {
                    if (layer instanceof L.Marker) {
                        layer.dragging.disable();
                        if (layer._icon) {
                            layer._icon.style.boxShadow = '';
                            layer._icon.style.border = '';
                        }
                    }
                });
            }

            // function actualizarLote(id) {
            //     let formData = new FormData($('#loteForm')[0]); 
            //     formData.append('_method', 'PUT');
                
            //     // ✅ AGREGAR LATITUD Y LONGITUD ACTUALIZADAS DEL FORMULARIO
            //     const latitud = $('#latitud').val();
            //     const longitud = $('#longitud').val();
            //     formData.append('latitud', latitud);
            //     formData.append('longitud', longitud);
                
            //     $.ajax({
            //         url: '/lotes/' + id,
            //         method: 'POST',
            //         data: formData,
            //         processData: false,
            //         contentType: false,
            //         headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            //         success: function(res) {
            //             if (res.success) {
            //                 toastr.success(res.message);
            //                 $('#loteForm')[0].reset();
            //                 $('#lote_id').val('');
            //                 $('#cancelarBtn').addClass('d-none');
                            
            //                 // ✅ DESACTIVAR ARRASTRE DESPUÉS DE GUARDAR
            //                 drawnItems.eachLayer(function(layer) {
            //                     if (layer instanceof L.Marker) {
            //                         layer.dragging.disable();
            //                         if (layer._icon) {
            //                             layer._icon.style.boxShadow = '';
            //                             layer._icon.style.border = '';
            //                         }
            //                     }
            //                 });
                            
            //                 cargarLotes(); // Recargar lotes para actualizar marcadores
            //             } else {
            //                 toastr.error(res.message);
            //             }
            //         },
            //         error: function(xhr) {
            //             console.error('Error al actualizar lote:', xhr.responseText);
            //             toastr.error('❌ Error al actualizar el lote');
            //         }
            //     });
            // }

            $('#cancelarBtn').on('click', function() {
                $('#loteForm')[0].reset();
                $('#lote_id').val('');
                $('#cancelarBtn').addClass('d-none');
                
                // ✅ DESACTIVAR ARRASTRE AL CANCELAR
                drawnItems.eachLayer(function(layer) {
                    if (layer instanceof L.Marker) {
                        layer.dragging.disable();
                        if (layer._icon) {
                            layer._icon.style.boxShadow = '';
                            layer._icon.style.border = '';
                        }
                    }
                });
            });

            // ✏️ Editar lote
            $(document).on('click', '.editarLote', function() {
                const id = $(this).data('id');
                $.getJSON(`/api/lotes`, function(lotes) {
                    let lote = lotes.find(l => l.id == id);
                    if (!lote) return;
                    
                    $('#lote_id').val(lote.id);
                    $('#codigo').val(lote.codigo);
                    $('#nombre').val(lote.nombre);
                    $('#area_m2').val(lote.area_m2);
                    $('#precio_m2').val(lote.precio_m2);
                    $('#latitud').val(lote.latitud);
                    $('#longitud').val(lote.longitud);
                    $('#estado_lote_id').val(lote.estado_lote_id);
                    $('#frente').val(lote.frente);
                    $('#lado_izquierdo').val(lote.lado_izquierdo);
                    $('#lado_derecho').val(lote.lado_derecho);
                    $('#fondo').val(lote.fondo);
                    $('#coordenadas').val(lote.coordenadas);
                    $('#cancelarBtn').removeClass('d-none');
                    
                    // ✅ HACER ARRASTRABLE SOLO EL MARCADOR QUE SE ESTÁ EDITANDO
                    drawnItems.eachLayer(function(layer) {
                        if (layer instanceof L.Marker) {
                            // Quitar arrastre y resaltado de todos los marcadores
                            layer.dragging.disable();
                            if (layer._icon) {
                                layer._icon.style.boxShadow = '';
                                layer._icon.style.border = '';
                            }
                            
                            // Activar arrastre y resaltar SOLO el marcador que se está editando
                            if (layer._loteId == lote.id) {
                                layer.dragging.enable(); // ✅ HACER ARRASTRABLE
                                
                                if (layer._icon) {
                                    layer._icon.style.boxShadow = '0 0 0 3px yellow, 0 0 10px rgba(255,255,0,0.5)';
                                    layer._icon.style.border = '2px solid orange';
                                }
                                
                                // ✅ ACTUALIZAR POSICIÓN EN FORMULARIO CUANDO SE ARRASTRA
                                layer.on('dragend', function(event) {
                                    const marker = event.target;
                                    const newLatLng = marker.getLatLng();
                                    
                                    // Actualizar campos del formulario
                                    $('#latitud').val(newLatLng.lat.toFixed(8));
                                    $('#longitud').val(newLatLng.lng.toFixed(8));
                                    
                                    console.log('📍 Posición temporal actualizada:', newLatLng);
                                });
                                
                                // Centrar mapa en el marcador
                                map.setView(layer.getLatLng(), map.getZoom());
                                
                                // Abrir popup
                                layer.openPopup();
                            }
                        }
                    });
                    
                    toastr.info('Editando lote ' + lote.codigo + ' - Arrastra el marcador para cambiar posición y luego guarda');
                });
            });

            // 🗑️ Eliminar lote
            $(document).on('click', '.eliminarLote', function() {
                const id = $(this).data('id');
                if (!confirm('¿Eliminar este lote?')) return;
                $.ajax({
                    url: `/lotes/${id}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        toastr.success(res.message);
                        cargarLotes();
                    },
                    error: function() {
                        toastr.error('Error al eliminar el lote');
                    }
                });
            });

            



        });
    </script>
@endsection


