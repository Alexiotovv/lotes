@extends('layouts.app')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 600px; }
</style>
@endsection

@section('content')
    <h3 class="mb-4">🗺️ Registrar Lotes Rápidos en el Mapa</h3>

    <!-- Select de prefijos y botón para nuevo prefijo -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Prefijo de Lote</label>
            <select id="prefijoSelect" class="form-select">
                @foreach($prefijos as $prefijo)
                    <option value="{{ $prefijo }}">{{ $prefijo }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoPrefijo">
                ➕ Nuevo Prefijo
            </button>
        </div>
    </div>

    <!-- Modal para nuevo prefijo -->
    <div class="modal fade" id="modalNuevoPrefijo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nuevo Prefijo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Letra del prefijo (A-Z)</label>
                        <input type="text" id="nuevoPrefijoInput" class="form-control" maxlength="1" style="text-transform: uppercase;">
                        <div class="text-danger mt-1" id="errorPrefijo" style="display:none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarPrefijoBtn">Agregar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <button id="guardarLotes" class="btn btn-success">💾 Guardar nuevos lotes</button>
        <button id="limpiarLotes" class="btn btn-danger">🧹 Limpiar locales</button>
    </div>

    <div id="map"></div>

    <p class="mt-3 text-muted">
        ➕ Haz clic en el mapa para agregar un marcador lote.<br>
        🔄 Arrastra un marcador para cambiar su posición.<br>
        ❌ Haz clic sobre un marcador para eliminarlo.<br>
    </p>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ✅ Cargar configuración desde la base de datos
    const mapConfig = @json($mapConfig ?? null);
    const imagenesSuperpuestas = @json($imagenesSuperpuestas ?? []);

    // ✅ Valores por defecto en caso de que no haya configuración
    const latInicial = mapConfig?.lat_map ? parseFloat(mapConfig.lat_map) : -3.844051;
    const lonInicial = mapConfig?.lon_map ? parseFloat(mapConfig.lon_map) : -73.3432986;
    const zoomInicial = mapConfig?.actual_zoom_map ? parseInt(mapConfig.actual_zoom_map) : 19;
    const maxZoom = mapConfig?.max_zoom_map ? parseInt(mapConfig.max_zoom_map) : 20;
    const minZoom = mapConfig?.min_zoom_map ? parseInt(mapConfig.min_zoom_map) : 15;

    console.log('🔧 Configuración del mapa cargada:', {
        latInicial, lonInicial, zoomInicial, maxZoom, minZoom
    });

    // ✅ Inicializar mapa con valores de la base de datos
    const map = L.map('map', {
        maxZoom: maxZoom,
        minZoom: minZoom
    }).setView([latInicial, lonInicial], zoomInicial);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: maxZoom,
        minZoom: minZoom,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // ✅ Función para cargar imágenes superpuestas
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
                
                // ✅ DEBUG DETALLADO
                console.log('📍 Imagen ID:', imgData.id, {
                    latCentro: latCentro,
                    lngCentro: lngCentro,
                    anchoLat: anchoLat,
                    anchoLng: anchoLng,
                    escala: escala,
                    bounds: [
                        [latCentro - halfLat, lngCentro - halfLng],
                        [latCentro + halfLat, lngCentro + halfLng]
                    ],
                    diferenciaVertical: `±${halfLat}`
                });

                const bounds = [
                    [latCentro - halfLat, lngCentro - halfLng],
                    [latCentro + halfLat, lngCentro + halfLng]
                ];

                // Cargar la imagen superpuesta
                const overlay = L.imageOverlay(imgData.url_completa, bounds, {
                    opacity: opacidad,
                    interactive: false
                }).addTo(map);

                // ✅ Forzar z-index y agregar ID visual para debug
                // ✅ SOLUCIÓN: Forzar z-index MÁS ALTO y posición absoluta
                overlay.on('add', function() {
                    const imgElement = overlay.getElement();
                    if (imgElement) {
                        // ✅ Z-INDEX MUCHO MÁS ALTO y !important
                        imgElement.style.zIndex = '9999';
                        imgElement.style.position = 'absolute';
                        imgElement.style.pointerEvents = 'none'; // Evitar conflictos
                        
                        console.log(`✅ Z-index aplicado a imagen ID ${imgData.id}: 9999`);
                    }
                });

                // ✅ También aplicar después de un delay por si acaso
                setTimeout(() => {
                    const imgElement = overlay.getElement();
                    if (imgElement) {
                        imgElement.style.zIndex = '9999';
                        imgElement.style.position = 'absolute';
                        
                        // ✅ DEBUG: Verificar que mantiene el z-index
                        console.log(`✅ Verificación imagen ID ${imgData.id}:`, {
                            zIndex: imgElement.style.zIndex,
                            position: imgElement.style.position,
                            display: imgElement.style.display
                        });
                    }
                }, 500);
                
            } catch (error) {
                console.error('Error cargando imagen superpuesta ID:', imgData.id, error);
            }
        });
    }

    // ✅ Cargar imágenes cuando el mapa esté listo
    map.whenReady(() => {
        setTimeout(() => {
            cargarImagenesSuperpuestas();
        }, 1000);
    });


    // ✅ SEPARAR: Lotes existentes vs Lotes nuevos
    let lotesExistentes = @json($lotes); // Estos ya están en BD - SOLO LECTURA
    let lotesNuevos = JSON.parse(localStorage.getItem('lotesMapa') || '[]'); // Estos son nuevos - SOLO ESTOS SE GUARDAN
    let markers = [];
    let prefijoActual = document.getElementById('prefijoSelect').value;

    // ✅ Cargar solo lotes existentes en el mapa (solo visual)
    lotesExistentes.forEach(l => {
        if (l.latitud && l.longitud) {
            const marker = L.marker([l.latitud, l.longitud], {
                draggable: false,
                icon: L.icon({
                    iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                    iconSize: [25, 25]
                })
            }).addTo(map);
            marker.bindPopup(`<b>${l.codigo}</b><br>Registrado`);
            // ✅ NO agregar a lotesNuevos - solo es visual
        }
    });

    // ✅ Generar código con correlativo dinámico (SOLO para nuevos)
    function generarCodigo(prefijo) {
        // Números existentes en la base de datos
        const numerosBD = lotesExistentes
            .filter(l => l.codigo && l.codigo.startsWith(prefijo))
            .map(l => {
                const match = l.codigo.match(new RegExp(`^${prefijo}(\\d+)$`));
                return match ? parseInt(match[1]) : 0;
            });
        
        // Números ya generados en esta sesión (solo los nuevos)
        const numerosSesion = lotesNuevos
            .filter(l => l.codigo && l.codigo.startsWith(prefijo))
            .map(l => {
                const match = l.codigo.match(new RegExp(`^${prefijo}(\\d+)$`));
                return match ? parseInt(match[1]) : 0;
            });
        
        // Encontrar el máximo
        const todosNumeros = [...numerosBD, ...numerosSesion];
        const maxNumero = todosNumeros.length > 0 ? Math.max(...todosNumeros) : 0;
        
        // Generar nuevo código
        const nuevoNumero = maxNumero + 1;
        return prefijo + nuevoNumero.toString().padStart(3, '0');
    }

    // Manejar cambio de prefijo
    document.getElementById('prefijoSelect').addEventListener('change', function() {
        prefijoActual = this.value;
    });

    // Guardar nuevo prefijo
    document.getElementById('guardarPrefijoBtn').addEventListener('click', function() {
        const input = document.getElementById('nuevoPrefijoInput');
        const error = document.getElementById('errorPrefijo');
        const valor = input.value.trim().toUpperCase();

        if (!valor || !/^[A-Z]$/.test(valor)) {
            error.textContent = 'Ingrese una letra válida (A-Z)';
            error.style.display = 'block';
            return;
        }

        // Verificar si ya existe
        const select = document.getElementById('prefijoSelect');
        if (Array.from(select.options).some(opt => opt.value === valor)) {
            error.textContent = 'Este prefijo ya existe';
            error.style.display = 'block';
            return;
        }

        // Agregar al select
        const option = document.createElement('option');
        option.value = valor;
        option.textContent = valor;
        select.appendChild(option);
        select.value = valor;
        prefijoActual = valor;

        // Limpiar y cerrar
        input.value = '';
        error.style.display = 'none';
        bootstrap.Modal.getInstance(document.getElementById('modalNuevoPrefijo')).hide();
    });

    // ✅ Agregar lote al hacer clic (SOLO a lotesNuevos)
    map.on('click', function(e) {
        // ✅ Validar que haya un prefijo seleccionado
        const selectPrefijo = document.getElementById('prefijoSelect');
        if (!selectPrefijo.value) {
            alert('⚠️ Primero debe seleccionar un prefijo de lote.');
            return;
        }

        if (lotesNuevos.length >= 100) { // Reducido a 100 por seguridad
            alert('⚠️ Límite de 100 lotes nuevos alcanzado.');
            return;
        }

        const codigo = generarCodigo(selectPrefijo.value);
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        
        // ✅ SOLO agregar a lotesNuevos
        const lote = { codigo, latitud: lat, longitud: lng };
        lotesNuevos.push(lote);
        localStorage.setItem('lotesMapa', JSON.stringify(lotesNuevos));

        const marker = L.marker([lat, lng], { 
            draggable: true,
            icon: L.icon({ // ✅ Icono diferente para nuevos lotes
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                iconSize: [25, 25]
            })
        }).addTo(map);
        
        marker.bindPopup(`
            <b>${codigo}</b><br>
            (${lat}, ${lng})<br>
            <span style="color: green;">🆕 NUEVO - No guardado</span><br>
            <i>Clic para eliminar</i>
        `).openPopup();
        
        markers.push(marker);

        // ✅ Eliminar marcador (solo de lotesNuevos)
        marker.on('click', function() {
            if (confirm(`¿Eliminar el marcador ${codigo}?`)) {
                const index = lotesNuevos.findIndex(l => l.codigo === codigo);
                if (index !== -1) {
                    lotesNuevos.splice(index, 1);
                    localStorage.setItem('lotesMapa', JSON.stringify(lotesNuevos));
                }
                map.removeLayer(marker);
                markers = markers.filter(m => m !== marker);
            }
        });

        // ✅ Mover marcador (solo actualizar lotesNuevos)
        marker.on('dragend', function(event) {
            const { lat, lng } = event.target.getLatLng();
            const index = lotesNuevos.findIndex(l => l.codigo === codigo);
            if (index !== -1) {
                lotesNuevos[index].latitud = lat.toFixed(6);
                lotesNuevos[index].longitud = lng.toFixed(6);
                localStorage.setItem('lotesMapa', JSON.stringify(lotesNuevos));
            }
            marker.setPopupContent(`
                <b>${codigo}</b><br>
                (${lat.toFixed(6)}, ${lng.toFixed(6)})<br>
                <span style="color: green;">🆕 NUEVO - No guardado</span><br>
                <i>Clic para eliminar</i>
            `);
        });
    });

    // ✅ Guardar SOLO lotes nuevos
    document.getElementById('guardarLotes').addEventListener('click', function() {
        const selectPrefijo = document.getElementById('prefijoSelect');
        if (!selectPrefijo.value) {
            alert('⚠️ Seleccione un prefijo antes de guardar.');
            return;
        }

        if (lotesNuevos.length === 0) {
            alert('No hay lotes nuevos por guardar.');
            return;
        }

        // ✅ DEBUG: Verificar qué se va a enviar
        console.log('📤 Enviando lotes NUEVOS:', lotesNuevos);
        console.log('📊 Total lotes existentes (NO se envían):', lotesExistentes.length);
        console.log('📊 Total lotes nuevos (SÍ se envían):', lotesNuevos.length);

        fetch("{{ route('mapa.guardar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                lotes: lotesNuevos // ✅ SOLO enviar lotes nuevos
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                // ✅ Limpiar SOLO los lotes nuevos después de guardar
                localStorage.removeItem('lotesMapa');
                lotesNuevos = [];
                
                // ✅ Eliminar SOLO los marcadores nuevos del mapa
                markers.forEach(m => map.removeLayer(m));
                markers = [];
                
                // ✅ Recargar la página para mostrar los nuevos lotes como existentes
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                
            } else {
                toastr.error(data.message || 'Error al guardar.');
            }
        })
        .catch(err => {
            console.error('❌ Error:', err);
            toastr.error('Error al conectar con el servidor.');
        });
    });

    // ✅ Limpiar SOLO lotes locales (nuevos)
    document.getElementById('limpiarLotes').addEventListener('click', function() {
        if (confirm('¿Seguro que deseas limpiar los lotes locales no guardados?')) {
            localStorage.removeItem('lotesMapa');
            lotesNuevos = [];
            
            // ✅ Eliminar SOLO los marcadores nuevos
            markers.forEach(m => map.removeLayer(m));
            markers = [];
            
            toastr.success('🧹 Lotes locales limpiados.');
        }
    });


</script>

@endsection
