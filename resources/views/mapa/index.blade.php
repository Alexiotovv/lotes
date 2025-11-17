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
            const map = L.map('map',{ maxZoom: 19 }).setView([-3.844051, -73.3432986], 19);

            var calle = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                minZoom: 15,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // 🗺️ Superponer imagen (plano) centrada en -3.844051, -73.3432986
            // Coordenadas aproximadas del área de cobertura (ajusta si tu imagen no encaja bien)
            const centro = [-3.844051, -73.3432986];

            // Calcula un área rectangular alrededor del punto central (en grados)
            // Aumenta o reduce el delta para ajustar el tamaño de la imagen
            const deltaLat = 0.0100; // norte-sur
            const deltaLng = 0.0120; // este-oeste

            const imageBounds = [
                [centro[0] - deltaLat, centro[1] - deltaLng], // suroeste
                [centro[0] + deltaLat, centro[1] + deltaLng]  // noreste
            ];

            // Cargar la imagen del plano
            L.imageOverlay('/img/plano.png', imageBounds, { opacity: 0.85 }).addTo(map);

            // Centrar el mapa en el área de la imagen
            // map.fitBounds(imageBounds);
            map.setView(centro, 19);
            //cierre codigo agregado para la imagen



            // L.control.layers({ "Calle": calle, "Satélite": satelite }).addTo(map);

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

                            const marker = L.marker([lote.latitud, lote.longitud], { icon: customIcon }).addTo(drawnItems);

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
                let formData = new FormData($('#loteForm')[0]); 
                formData.append('_method', 'PUT');
                $.ajax({
                    url: '/lotes/' + id,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $('#loteForm')[0].reset();
                            $('#lote_id').val('');
                            $('#cancelarBtn').addClass('d-none');
                            cargarLotes();
                        } else toastr.error(res.message);
                    },
                    error: () => toastr.error('❌ Error al actualizar el lote'),
                });
            }

            $('#cancelarBtn').on('click', function() {
                $('#loteForm')[0].reset();
                $('#lote_id').val('');
                $('#cancelarBtn').addClass('d-none');
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
                    toastr.info('Editando lote ' + lote.codigo);
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


