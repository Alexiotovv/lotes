@extends('layouts.app')

@section('css')
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const map = L.map('map',{ maxZoom: 20 }).setView([-3.844051, -73.3432986], 19);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Superponer imagen
    const centro = [-3.844051, -73.3432986];
    const deltaLat = 0.0100;
    const deltaLng = 0.0120;
    const imageBounds = [
        [centro[0] - deltaLat, centro[1] - deltaLng],
        [centro[0] + deltaLat, centro[1] + deltaLng]
    ];
    L.imageOverlay('/img/plano.png', imageBounds, { opacity: 0.85 }).addTo(map);
    map.setView(centro, 19);

    // Variables globales
    let lotes = JSON.parse(localStorage.getItem('lotesMapa') || '[]');
    let markers = [];
    let prefijoActual = document.getElementById('prefijoSelect').value;

    // Cargar lotes existentes
    const lotesExistentes = @json($lotes);
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
        }
    });

    // ✅ Obtener último número para el prefijo actual
    function obtenerUltimoNumero(prefijo) {
        const codigos = lotesExistentes
            .filter(l => l.codigo && l.codigo.startsWith(prefijo))
            .map(l => l.codigo);
        
        const numeros = codigos.map(c => parseInt(c.substring(1)) || 0);
        return Math.max(0, ...numeros);
    }

    // ✅ Generar código con correlativo dinámico
    function generarCodigo(prefijo) {
        // Números existentes en la base de datos
        const numerosBD = lotesExistentes
            .filter(l => l.codigo && l.codigo.startsWith(prefijo))
            .map(l => parseInt(l.codigo.substring(1)) || 0);
        
        // Números ya generados en esta sesión
        const numerosSesion = lotes
            .filter(l => l.codigo && l.codigo.startsWith(prefijo))
            .map(l => parseInt(l.codigo.substring(1)) || 0);
        
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

    // Agregar lote al hacer clic
    map.on('click', function(e) {
        if (lotes.length >= 100) {
            alert('⚠️ Límite de 100 lotes alcanzado.');
            return;
        }

        const codigo = generarCodigo(prefijoActual);
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        const lote = { codigo, latitud: lat, longitud: lng };
        lotes.push(lote);
        localStorage.setItem('lotesMapa', JSON.stringify(lotes));

        const marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.bindPopup(`<b>${codigo}</b><br>(${lat}, ${lng})<br><i>Clic para eliminar</i>`).openPopup();
        markers.push(marker);

        // Eliminar marcador
        marker.on('click', function() {
            if (confirm(`¿Eliminar el marcador ${codigo}?`)) {
                const index = lotes.findIndex(l => l.codigo === codigo);
                if (index !== -1) lotes.splice(index, 1);
                localStorage.setItem('lotesMapa', JSON.stringify(lotes));
                map.removeLayer(marker);
                markers = markers.filter(m => m !== marker);
            }
        });

        // Mover marcador
        marker.on('dragend', function(event) {
            const { lat, lng } = event.target.getLatLng();
            const index = lotes.findIndex(l => l.codigo === codigo);
            if (index !== -1) {
                lotes[index].latitud = lat.toFixed(6);
                lotes[index].longitud = lng.toFixed(6);
                localStorage.setItem('lotesMapa', JSON.stringify(lotes));
            }
            marker.setPopupContent(`<b>${codigo}</b><br>(${lat.toFixed(6)}, ${lng.toFixed(6)})<br><i>Clic para eliminar</i>`);
        });
    });

    // Guardar lotes
    document.getElementById('guardarLotes').addEventListener('click', function() {
        if (lotes.length === 0) {
            alert('No hay lotes nuevos por guardar.');
            return;
        }

        fetch("{{ route('mapa.guardar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ lotes })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                localStorage.removeItem('lotesMapa');
                lotes = [];
                markers.forEach(m => map.removeLayer(m));
                markers = [];
            } else {
                alert(data.message || 'Error al guardar.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al conectar con el servidor.');
        });
    });

    // Limpiar lotes locales
    document.getElementById('limpiarLotes').addEventListener('click', function() {
        if (confirm('¿Seguro que deseas limpiar los lotes locales no guardados?')) {
            localStorage.removeItem('lotesMapa');
            lotes = [];
            markers.forEach(m => map.removeLayer(m));
            markers = [];
            alert('🧹 Lotes locales limpiados.');
        }
    });
</script>

@endsection
