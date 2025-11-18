@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        #controlesEliminar {
            position: absolute;
            top: 120px; /* Debajo de los controles de zoom */
            right: 20px;
            z-index: 1000;
        }

        .btn-eliminar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background-color: #dc3545;
            color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-eliminar:hover {
            background-color: #c82333;
        }

        .btn-eliminar:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        #contenedorMapa {
            position: relative;
            width: 100%;
            height: 600px;
            border: 1px solid #ccc;
            overflow: hidden; /* Evitar scroll del contenedor */
        }

        #mapaFondo {
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        #imagenEditable {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Centrado inicial */
            max-width: none;
            cursor: move;
            user-select: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            z-index: 2; /* Siempre encima del mapa */
            pointer-events: auto; /* Permitir eventos de mouse sobre la imagen */
        }

        /* Controles sobre el mapa */
        #controlesZoom {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-zoom {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background-color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-zoom:hover {
            background-color: #f0f0f0;
        }

        #controlesMovimiento {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .btn-mover {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background-color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-mover:hover {
            background-color: #f0f0f0;
        }

        #infoEstado {
            position: absolute;
            top: 10px;
            left: 50px;
            z-index: 1000;
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
        }
        #controlesOpacidad {
            position: absolute;
            top: 170px; /* Debajo del botón eliminar */
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .control-opacidad {
            writing-mode: bt-lr; /* Vertical */
            -webkit-appearance: slider-vertical;
            width: 8px;
            height: 80px;
            padding: 0 5px;
        }

        .label-opacidad {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }

    </style>
@endsection

@section('content')
    <h3>🖼️ Editor de Imagen sobre Mapa</h3>

    <div class="mb-3">
        <label for="archivoImagen">Seleccionar Imagen:</label>
        <input type="file" id="archivoImagen" accept="image/*">
    </div>

    <div id="contenedorMapa">
        <!-- ✅ Mapa de fondo -->
        <div id="mapaFondo"></div>
        <!-- Panel de información del mapa -->
        <div id="infoMapa" class="position-fixed bottom-0 start-0 end-0 bg-dark text-white p-2 small" style="z-index: 1000; opacity: 0.9;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <strong>📍 Lat:</strong> <span id="latActual">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>📍 Lon:</strong> <span id="lonActual">-</span>
                    </div>
                    <div class="col-md-4">
                        <strong>🔍 Zoom:</strong> <span id="zoomActual">-</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ✅ Imagen editable encima del mapa -->
        <img id="imagenEditable" src="" alt="Imagen editable" style="display:none;">

        <!-- Controles de zoom -->
        <div id="controlesZoom">
            <button class="btn-zoom" id="btnZoomIn">+</button>
            <button class="btn-zoom" id="btnZoomOut">−</button>
            <button class="btn-zoom" id="btnReset">↺</button>
        </div>
        <!-- Controles de eliminar -->
        <div id="controlesEliminar">
            <button class="btn-eliminar" id="btnEliminarImagen" title="Eliminar imagen seleccionada" disabled>×</button>
        </div>
        <!-- Controles de opacidad -->
        <div id="controlesOpacidad" hidden>
            <span class="label-opacidad">Op.</span>
            <input type="range" 
                id="controlOpacidad" 
                class="control-opacidad" 
                orient="vertical"
                min="0.3" 
                max="1.0" 
                step="0.05" 
                value="0.85"
                title="Ajustar opacidad de la imagen">
        </div>
        <!-- Controles de movimiento -->
        <div id="controlesMovimiento">
            <button class="btn-mover btn-arriba" id="btnArriba">↑</button>
            <div class="d-flex">
                <button class="btn-mover btn-izquierda" id="btnIzquierda">←</button>
                <button class="btn-mover btn-derecha" id="btnDerecha">→</button>
            </div>
            <button class="btn-mover btn-abajo" id="btnAbajo">↓</button>
        </div>

        <!-- Información de estado -->
        <div id="infoEstado">
            Escala: <span id="valorEscala">1.00</span> | Posición: X=<span id="posX">0</span>, Y=<span id="posY">0</span>
        </div>
    </div>

    <button id="btnGuardarPosicion" class="btn btn-success mt-3">💾 Guardar Posición</button>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ✅ Cargar valores desde la base de datos
        const mapData = @json($mapImage);

        const latInicial = Number(mapData?.lat_map) || -3.844051;
        const lonInicial = Number(mapData?.lon_map) || -73.3432986;

        const zoomInicial = mapData?.actual_zoom_map || 19; // Asumiendo que agregará este campo
        const maxZoom = mapData?.max_zoom_map || 19;
        const minZoom = mapData?.min_zoom_map || 15;

        // ✅ Inicializar mapa con valores de la base de datos
        const mapa = L.map('mapaFondo', {
            maxZoom: maxZoom,
            minZoom: minZoom
        }).setView([latInicial, lonInicial], zoomInicial);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: maxZoom,
            minZoom: minZoom,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapa);

        // let overlayImagen = null;
        let overlays = [];  // ← aquí estarán todas las imágenes que subas
        let overlaySeleccionado = null;  // ← esta es la imagen activa

        let centroImg = [latInicial, lonInicial]; // Centro de la imagen
        let anchoLat = 0.0100;  // Tamaño vertical
        let anchoLng = 0.0120;  // Tamaño horizontal
        let escalaImg = 1;      // Zoom de la imagen

        function actualizarOverlay() {
            if (!overlaySeleccionado) return;
            const centroImg2 = mapa.getCenter();
            
            let halfLat = (anchoLat * escalaImg) / 2;
            let halfLng = (anchoLng * escalaImg) / 2;

            let newBounds = [
                [centroImg2[0] - halfLat, centroImg2[1] - halfLng],
                [centroImg2[0] + halfLat, centroImg2[1] + halfLng]
            ];

            overlaySeleccionado.setBounds(newBounds);
        }

        // ✅ Actualizar panel de información en tiempo real
        mapa.on('moveend zoomend', function() {
            const center = mapa.getCenter();
            const zoom = mapa.getZoom();

            document.getElementById('latActual').textContent = center.lat.toFixed(7);
            document.getElementById('lonActual').textContent = center.lng.toFixed(7);
            document.getElementById('zoomActual').textContent = zoom;
        });

        // ✅ Al cargar la página, mostrar valores iniciales
        setTimeout(() => {
            const center = mapa.getCenter();
            const zoom = mapa.getZoom();
            document.getElementById('latActual').textContent = center.lat.toFixed(7);
            document.getElementById('lonActual').textContent = center.lng.toFixed(7);
            document.getElementById('zoomActual').textContent = zoom;
        }, 100);

        // ✅ Botón de guardar posición del mapa
        $('#btnGuardarPosicion').on('click', function() {
            const center = mapa.getCenter();
            const zoom = mapa.getZoom();

            const datos = {
                lat_map: center.lat,
                lon_map: center.lng,
                zoom_actual: zoom,
                _token: '{{ csrf_token() }}' // ✅ Token incluido en los datos
            };

            $.ajax({
                url: '{{ route("mapa.actualizar.posicion") }}',
                method: 'POST',
                data: datos,
                success: function(response) {
                    if(response.success) {
                        alert('✅ Posición del mapa guardada correctamente.');
                    } else {
                        alert('❌ Error: ' + (response.message || 'Desconocido'));
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    alert('❌ Error de conexión o servidor: ' + xhr.status);
                }
            });
        });

        

       // ... código anterior igual ...

        $('#archivoImagen').on('change', function(e) {
            const archivo = e.target.files[0];
            if (!archivo) return;

            const lector = new FileReader();

            lector.onload = function(evt) {
                const base64 = evt.target.result;
                const center = mapa.getCenter();
                const halfLat = 0.0050;
                const halfLng = 0.0060;

                const imageBounds = [
                    [center.lat - halfLat, center.lng - halfLng],
                    [center.lat + halfLat, center.lng + halfLng]
                ];

                // Crear overlay temporal (solo para preview inmediato)
                let newOverlay = L.imageOverlay(base64, imageBounds, { 
                    opacity: 0.85
                }).addTo(mapa);

                // METADATOS temporales
                newOverlay._meta = {
                    anchoLat: halfLat * 2,
                    anchoLng: halfLng * 2,
                    escala: 1,
                    deltaMovimiento: 0.0003,
                    opacidad: 0.85,
                    lat: center.lat,
                    lon: center.lng
                };

                overlays.push(newOverlay);
                activarOverlay(newOverlay);

                // ✅ GUARDAR EN BASE DE DATOS Y RECARGAR
                guardarImagenSuperpuestaEnBD(newOverlay, base64);

                newOverlay.once('load', () => {
                    const el = newOverlay.getElement();
                    if (el) {
                        el.style.pointerEvents = "auto";
                        el.style.cursor = "pointer";
                        el.addEventListener("click", function(e) {
                            e.stopPropagation();
                            activarOverlay(newOverlay);
                        });
                    }
                });

                mapa.fitBounds(imageBounds);
            };
            lector.readAsDataURL(archivo);
        });

        // ✅ Función modificada para guardar y recargar
        function guardarImagenSuperpuestaEnBD(overlay, base64Data) {
            const meta = overlay._meta;
            
            const datos = {
                image_data: base64Data,
                lat_centro: meta.lat,
                lng_centro: meta.lon,
                ancho_lat: meta.anchoLat,
                ancho_lng: meta.anchoLng,
                escala: meta.escala,
                opacidad: meta.opacidad,
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: '{{ route("imagen.superpuesta.guardar") }}',
                method: 'POST',
                data: datos,
                success: function(response) {
                    if(response.success) {
                        console.log('✅ Imagen guardada en BD, recargando página...');
                        
                        // Recargar la página después de guardar
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000); // Pequeño delay para ver el mensaje
                    }
                },
                error: function(xhr) {
                    console.error('Error al guardar imagen:', xhr.responseText);
                    alert('Error al guardar la imagen');
                }
            });
        }

        // ✅ Botón eliminar imagen seleccionada - MODIFICADO
        $('#btnEliminarImagen').on('click', function() {
            if (!overlaySeleccionado || !overlaySeleccionado._meta.id) return;
            
            if (confirm('¿Estás seguro de que quieres eliminar esta imagen?')) {
                // Eliminar de la base de datos
                $.ajax({
                    url: '/mapa/imagen-superpuesta/' + overlaySeleccionado._meta.id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if(response.success) {
                            // Remover del array de overlays
                            const index = overlays.indexOf(overlaySeleccionado);
                            if (index > -1) {
                                overlays.splice(index, 1);
                            }
                            
                            // Remover del mapa
                            mapa.removeLayer(overlaySeleccionado);
                            
                            // Resetear selección
                            overlaySeleccionado = null;
                            
                            // Deshabilitar botón eliminar
                            $('#btnEliminarImagen').prop('disabled', true);
                            
                            console.log('✅ Imagen eliminada de la BD');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al eliminar imagen:', xhr.responseText);
                        alert('Error al eliminar la imagen');
                    }
                });
            }
        });

        // ✅ Actualizar estado del botón eliminar cuando se selecciona una imagen
        function activarOverlay(overlay) {
        overlaySeleccionado = overlay;

        // Remover outline de todas las imágenes
        overlays.forEach(o => {
            if (o.getElement()) o.getElement().style.outline = "none";
        });

        // Aplicar outline a la imagen seleccionada
        if (overlay.getElement()) {
            overlay.getElement().style.outline = "3px solid red";
        }

        // Habilitar botón eliminar
        $('#btnEliminarImagen').prop('disabled', false);

        // ✅ SINCRONIZAR SLIDER DE OPACIDAD con la imagen seleccionada
        if (overlaySeleccionado && overlaySeleccionado._meta) {
            const opacidadActual = overlaySeleccionado._meta.opacidad || 0.85;
            $('#controlOpacidad').val(opacidadActual);
                // Actualizar también el overlay visualmente (por si acaso)
                overlaySeleccionado.setOpacity(opacidadActual);
                console.log('Slider de opacidad sincronizado:', opacidadActual);
                }
            }

    // ✅ Deshabilitar botón eliminar al cargar la página
    $(document).ready(function() {
        $('#btnEliminarImagen').prop('disabled', true);
    });

    // FUNCIÓN CORREGIDA - usa metadatos específicos de cada overlay
    function actualizarOverlaySeleccionado(deltaLat, deltaLng, zoomDelta = 0) {
        if (!overlaySeleccionado || !overlaySeleccionado._meta) return;
        
        let bounds = overlaySeleccionado.getBounds();
        let meta = overlaySeleccionado._meta;

        let lat1 = bounds.getSouthWest().lat + deltaLat;
        let lng1 = bounds.getSouthWest().lng + deltaLng;
        let lat2 = bounds.getNorthEast().lat + deltaLat;
        let lng2 = bounds.getNorthEast().lng + deltaLng;

        if (zoomDelta !== 0) {
            meta.escala += zoomDelta;
            if (meta.escala < 0.2) meta.escala = 0.2;
            if (meta.escala > 5) meta.escala = 5;

            const centerLat = (lat1 + lat2) / 2;
            const centerLng = (lng1 + lng2) / 2;

            const halfLat = (meta.anchoLat * meta.escala) / 2;
            const halfLng = (meta.anchoLng * meta.escala) / 2;

            lat1 = centerLat - halfLat;
            lat2 = centerLat + halfLat;
            lng1 = centerLng - halfLng;
            lng2 = centerLng + halfLng;
        }

        overlaySeleccionado.setBounds([
            [lat1, lng1],
            [lat2, lng2]
        ]);

        // ✅ ACTUALIZAR EN BD después de modificar
        if (overlaySeleccionado._meta.id) {
            setTimeout(() => {
                actualizarImagenEnBD(overlaySeleccionado);
            }, 500); // Debounce para no saturar
        }
    }

    // Botones de movimiento - usar delta del overlay seleccionado
    $('#btnArriba').click(() => {
        if (overlaySeleccionado && overlaySeleccionado._meta) {
            actualizarOverlaySeleccionado(overlaySeleccionado._meta.deltaMovimiento, 0);
        }
    });

    $('#btnAbajo').click(() => {
        if (overlaySeleccionado && overlaySeleccionado._meta) {
            actualizarOverlaySeleccionado(-overlaySeleccionado._meta.deltaMovimiento, 0);
        }
    });

    $('#btnIzquierda').click(() => {
        if (overlaySeleccionado && overlaySeleccionado._meta) {
            actualizarOverlaySeleccionado(0, -overlaySeleccionado._meta.deltaMovimiento);
        }
    });

    $('#btnDerecha').click(() => {
        if (overlaySeleccionado && overlaySeleccionado._meta) {
            actualizarOverlaySeleccionado(0, overlaySeleccionado._meta.deltaMovimiento);
        }
    });

    // Botones de zoom
    $('#btnZoomIn').click(() => actualizarOverlaySeleccionado(0, 0, +0.05));
    $('#btnZoomOut').click(() => actualizarOverlaySeleccionado(0, 0, -0.05));

    // ✅ Control de opacidad - VERSIÓN MEJORADA
    $('#controlOpacidad').on('input change', function() {
        const opacidad = parseFloat($(this).val());
        
        console.log('Slider movido - Opacidad:', opacidad, 'Imagen seleccionada:', overlaySeleccionado ? 'Sí' : 'No');
        
        if (overlaySeleccionado) {
            // Aplicar a la imagen seleccionada
            overlaySeleccionado.setOpacity(opacidad);
            overlaySeleccionado._meta.opacidad = opacidad;
            
            console.log('✅ Opacidad aplicada a imagen seleccionada:', opacidad);
            
            // ✅ ACTUALIZAR EN BD
            if (overlaySeleccionado._meta.id) {
                setTimeout(() => {
                    actualizarImagenEnBD(overlaySeleccionado);
                }, 500);
            }
        } else {
            // Aplicar a todas las imágenes si ninguna está seleccionada
            overlays.forEach(overlay => {
                overlay.setOpacity(opacidad);
                if (overlay._meta) {
                    overlay._meta.opacidad = opacidad;
                }
            });
            console.log('✅ Opacidad aplicada a todas las imágenes:', opacidad);
        }
    });
      
    // ✅ Cargar imágenes guardadas al iniciar la página
    const imagenesGuardadas = @json($imagenesSuperpuestas ?? []);

    
    // Función corregida para cargar una imagen desde la base de datos
    function cargarImagenDesdeBD(imgData) {
        return new Promise((resolve, reject) => {
            // ✅ Asegurar que los valores sean números
            const anchoLat = parseFloat(imgData.ancho_lat) || 0.0100;
            const anchoLng = parseFloat(imgData.ancho_lng) || 0.0120;
            const escala = parseFloat(imgData.escala) || 1.0;
            const latCentro = parseFloat(imgData.lat_centro);
            const lngCentro = parseFloat(imgData.lng_centro);
            
            const halfLat = (anchoLat * escala) / 2;
            const halfLng = (anchoLng * escala) / 2;
            
            // ✅ Verificar que los valores sean válidos
            if (isNaN(latCentro) || isNaN(lngCentro) || isNaN(halfLat) || isNaN(halfLng)) {
                console.error('Datos inválidos para imagen ID:', imgData.id, {
                    latCentro, lngCentro, halfLat, halfLng
                });
                reject(new Error('Datos de coordenadas inválidos'));
                return;
            }

            const bounds = [
                [latCentro - halfLat, lngCentro - halfLng],
                [latCentro + halfLat, lngCentro + halfLng]
            ];

            // ✅ Verificar que los bounds sean válidos
            if (bounds.some(coord => coord.some(isNaN))) {
                console.error('Bounds inválidos para imagen ID:', imgData.id, bounds);
                reject(new Error('Bounds inválidos'));
                return;
            }

            console.log('Cargando imagen ID:', imgData.id, 'Bounds:', bounds);

            // Usar la URL completa de la imagen
            let overlay = L.imageOverlay(imgData.url_completa, bounds, {
                opacity: parseFloat(imgData.opacidad) || 0.85
            }).addTo(mapa);

            // METADATOS completos - asegurar que sean números
            overlay._meta = {
                id: imgData.id,
                anchoLat: anchoLat,
                anchoLng: anchoLng,
                escala: escala,
                deltaMovimiento: 0.0003,
                opacidad: parseFloat(imgData.opacidad) || 0.85,
                lat: latCentro,
                lon: lngCentro,
                rutaImagen: imgData.ruta_imagen
            };

            overlays.push(overlay);
            
            // Agregar evento click para seleccionar
            // En la función cargarImagenDesdeBD, asegura la opacidad:
            overlay.once('load', () => {
                const el = overlay.getElement();
                if (el) {
                    el.style.pointerEvents = "auto";
                    el.style.cursor = "pointer";
                    el.addEventListener("click", function(e) {
                        e.stopPropagation();
                        activarOverlay(overlay);
                    });
                    
                    // ✅ ASEGURAR OPACIDAD INICIAL
                    const opacidadInicial = overlay._meta.opacidad || 0.85;
                    overlay.setOpacity(opacidadInicial);
                }
                console.log('✅ Imagen cargada correctamente ID:', imgData.id, 'Opacidad:', overlay._meta.opacidad);
                resolve(overlay);
            });

            overlay.on('error', (err) => {
                console.error('Error cargando imagen ID:', imgData.id, err);
                // Remover el overlay si hay error
                mapa.removeLayer(overlay);
                const index = overlays.indexOf(overlay);
                if (index > -1) {
                    overlays.splice(index, 1);
                }
                reject(new Error('Error cargando imagen: ' + err.message));
            });
        });
    }

    // ✅ Cargar todas las imágenes al iniciar - versión mejorada
    async function cargarTodasLasImagenes() {
        if (!imagenesGuardadas || imagenesGuardadas.length === 0) {
            console.log('No hay imágenes guardadas para cargar');
            return;
        }

        console.log('Cargando', imagenesGuardadas.length, 'imágenes desde la BD...');
        
        let loadedCount = 0;
        let errorCount = 0;
        
        for (const imgData of imagenesGuardadas) {
            try {
                await cargarImagenDesdeBD(imgData);
                loadedCount++;
            } catch (error) {
                console.error('Error cargando imagen ID:', imgData.id, error.message);
                errorCount++;
            }
        }
        
        console.log(`✅ Carga completada: ${loadedCount} exitosas, ${errorCount} errores`);
        
        if (errorCount > 0) {
            console.warn(`${errorCount} imágenes no pudieron cargarse. Verifica la consola para más detalles.`);
        }
    }

    // Ejecutar cuando el mapa esté listo
    mapa.whenReady(() => {
        setTimeout(() => {
            cargarTodasLasImagenes();
        }, 500);
    });

    // ✅ Función para actualizar los datos de una imagen en BD
    function actualizarImagenEnBD(overlay) {
        if (!overlay || !overlay._meta || !overlay._meta.id) return;
        
        const bounds = overlay.getBounds();
        const center = bounds.getCenter();
        const meta = overlay._meta;

        const datos = {
            lat_centro: center.lat,
            lng_centro: center.lng,
            ancho_lat: meta.anchoLat,
            ancho_lng: meta.anchoLng,
            escala: meta.escala,
            opacidad: meta.opacidad,
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '/mapa/imagen-superpuesta/actualizar/' + overlay._meta.id,
            method: 'PUT',
            data: datos,
            success: function(response) {
                if(response.success) {
                    console.log('✅ Imagen actualizada en BD');
                }
            },
            error: function(xhr) {
                console.error('Error al actualizar imagen:', xhr.responseText);
            }
        });
    }

    // ✅ Función para forzar actualización visual de opacidad
    function forzarActualizacionOpacidad() {
        if (overlaySeleccionado) {
            const opacidad = overlaySeleccionado._meta.opacidad || 0.85;
            overlaySeleccionado.setOpacity(opacidad);
            console.log('Opacidad forzada:', opacidad);
        }
    }

    

    </script>
  
@endsection