@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 80vh; width: 100%; border-radius: 10px; }
    .modal-lote { max-width: 400px; }
    .modal-lote .modal-content {
        border-radius: 10px;
        overflow: hidden;
    }
    .modal-lote .modal-header {
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        padding: 1rem 1.5rem;
    }
    .modal-lote .modal-body {
        padding: 1.5rem;
        font-family: Arial, sans-serif;
        color: #333;
    }
    .modal-title {
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        margin: 0.5rem 0;
        align-items: center;
    }
    .info-label {
        font-weight: bold;
        text-align: right;
    }
    .info-value {
        text-align: left;
    }
    .highlight {
        background-color: #ff9900;
        color: white;
        text-align: center;
        padding: 1rem;
        font-weight: bold;
        font-size: 16px;
    }
    .section-title {
        background-color: #333;
        color: white;
        text-align: center;
        padding: 0.5rem;
        font-size: 14px;
        font-weight: bold;
        margin: 1rem 0 0.5rem 0;
    }
    .footer-buttons {
        display: flex;
        justify-content: space-around;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #ddd;
    }
    .btn-footer {
        color: #ff9900;
        font-weight: bold;
        text-decoration: underline;
        padding: 0.5rem;
        font-size: 14px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <h3 class="mb-3">🗺️ Vista de Lotes - {{$empresa->nombre}}</h3>
    <div id="map"></div>
</div>

<!-- Modal de Lote -->
<div class="modal fade" id="modalLote" tabindex="-1">
    <div class="modal-dialog modal-lote">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title" id="modalTitle">Manzana C - Lote 8</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map', { maxZoom: 20 }).setView([-3.844051, -73.3432986], 19);

    // Capa base
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap contributors'
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

    // Cargar lotes
    const lotes = @json($lotes);
    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    lotes.forEach(lote => {
        if (!lote.latitud || !lote.longitud) return;

        // Icono por estado
        let iconUrl = '/img/markers/marker-icon-blue.png';
        if (lote.estado_lote?.estado?.toLowerCase() === 'disponible') iconUrl = '/img/markers/marker-icon-green.png';
        else if (lote.estado_lote?.estado?.toLowerCase() === 'reservado') iconUrl = '/img/markers/marker-icon-yellow.png';
        else if (lote.estado_lote?.estado?.toLowerCase() === 'vendido') iconUrl = '/img/markers/marker-icon-orange.png';
        else if (lote.estado_lote?.estado?.toLowerCase() === 'bloqueado') iconUrl = '/img/markers/marker-icon-red.png';

        const customIcon = L.icon({
            iconUrl: iconUrl,
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const marker = L.marker([lote.latitud, lote.longitud], { icon: customIcon }).addTo(drawnItems);

        // Abrir modal al hacer clic
        marker.on('click', () => mostrarModalLote(lote));
    });

    // Función para mostrar el modal
    function mostrarModalLote(lote) {
        const codigo = lote.codigo || 'SIN CÓDIGO';
        const nombre = lote.nombre || '';
        const titulo = `${codigo} - ${nombre}`.trim();
        const estadoLote = lote.estado_lote?.estado?.toLowerCase() || 'sin estado';

        let contenido = '';

        if (estadoLote === 'disponible') {
            const frente = parseFloat(lote.frente) || 0;
            const fondo = parseFloat(lote.fondo) || 0;
            const area = parseFloat(lote.area_m2) || 0;
            const precioM2 = parseFloat(lote.precio_m2) || 0;
            const precioTotal = area * precioM2;
            const cuotaMensual = precioTotal / 36;

            const formatoMoneda = (num) => num.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const formatoMedida = (num) => num > 0 ? num.toFixed(2) : '—';

            contenido = `
                <div class="text-center mb-2" style="color: #ff9900; font-weight: bold;">
                    FREnte Y FONDO ${formatoMedida(frente)} x ${formatoMedida(fondo)} MTS2
                </div>
                
                <div class="section-title">
                    LOTES DE ${formatoMedida(area)} MTS CUADRADOS
                </div>
                
                <div class="text-center my-3" style="font-size: 24px; font-weight: bold;">
                    S/ ${formatoMoneda(precioTotal)}
                </div>
                
                <div class="text-center mb-2" style="color: #ff9900; font-weight: bold;">
                    INICIAL
                </div>
                <div class="text-center mb-3" style="font-size: 16px; font-weight: bold;">
                    S/ 0.00
                </div>
                
                <div class="section-title">
                    FINANCIAMIENTO 0% INTERESES
                </div>
                
                <div class="highlight">
                    36 MESES
                </div>
                <div class="highlight">
                    S/ ${formatoMoneda(cuotaMensual)}
                </div>
                
                <div class="footer-buttons" style="flex-direction: column; gap: 0.75rem;">
                    <a class="btn-footer" href="" style="text-decoration: none; color: #17a2b8; font-weight: bold; text-align: center;">
                        📌 RESERVAR
                    </a>
                    <a class="btn-footer" href="{{ route('ventas.create') }}" style="text-decoration: none; color: #28a745; font-weight: bold; text-align: center;">
                        💰 VENDER
                    </a>
                    <a class="btn-footer" href="#" data-bs-dismiss="modal" style="text-decoration: none; color: #dc3545; font-weight: bold; text-align: center;">
                        ❌ CERRAR
                    </a>
                </div>
            `;
        } else {
            const estadoMostrar = lote.estado_lote?.estado || 'No disponible';
            const colorEstado = lote.estado_lote?.color || '#6c757d';

            contenido = `
                <div class="text-center my-4" style="font-size: 18px; font-weight: bold; color: ${colorEstado};">
                    ${estadoMostrar.toUpperCase()}
                </div>
                
                <div class="footer-buttons">
                    <a class="btn-footer" href="#" data-bs-dismiss="modal">CERRAR</a>
                </div>
            `;
        }

        document.getElementById('modalTitle').textContent = titulo;
        document.getElementById('modalBody').innerHTML = contenido;
        const modal = new bootstrap.Modal(document.getElementById('modalLote'));
        modal.show();
    }
});
</script>
@endsection