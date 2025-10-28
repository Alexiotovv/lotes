<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Plano en Mapa</title>
  <!-- ENLACE LOCAL para Leaflet CSS -->
  <link rel="stylesheet" href="{{ asset('css/leaflet-plugins/leaflet.css') }}" />
  <!-- ENLACE LOCAL para CSS de DistortableImage -->
  <link rel="stylesheet" href="{{ asset('dist/leaflet.distortableimage.css') }}" />
  <style>
    #map { height: 90vh; width: 100%; }
    body { margin: 0; font-family: sans-serif; }
    #guardar {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>

<h3 style="text-align:center">🗺️ Editor de Imagen del Mapa</h3>

@if(!$mapImage)
<form action="{{ route('map.store') }}" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="file" name="image" required>
  <button type="submit">Subir Imagen</button>
</form>
@else
<div id="map"></div>
<button id="guardar">💾 Guardar posición</button>
@endif

<!-- ENLACE LOCAL para Leaflet JS (¡PRIMERO!) -->
<script src="{{ asset('js/leaflet-plugins/leaflet.js') }}"></script>
<!-- Luego, ENLACE LOCAL para JS de DistortableImage -->
<script src="{{ asset('dist/leaflet.distortableimage.js') }}"></script>

@if($mapImage)
<script>
const map = L.map('map').setView([-3.844051, -73.3432986], 19);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 21,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const imageUrl = "{{ asset('storage/' . $mapImage->image_path) }}";

let savedPosition = {!! json_encode($position) !!};

let imageOverlay;

const distortableImageOptions = {
    translation: {
        deleteImage: 'Eliminar Imagen',
        distortImage: 'Distorsionar',
        dragImage: 'Mover',
        exportImage: 'Exportar',
        rotateImage: 'Rotar',
        scaleImage: 'Escalar',
        freeRotateImage: 'Girar/Escalar Libre',
        lockMode: 'Bloquear',
        confirmImageDelete: '¿Estás seguro de que quieres eliminar esta imagen del mapa?',
    }
};

if (savedPosition && savedPosition.length === 4) {
    const cornersLatLng = savedPosition.map(p => L.latLng(p[0], p[1]));

    imageOverlay = L.distortableImageOverlay(imageUrl, {
        ...distortableImageOptions,
        corners: cornersLatLng,
    }).addTo(map);
} else {
    imageOverlay = L.distortableImageOverlay(imageUrl, {
        ...distortableImageOptions,
    }).addTo(map);

    imageOverlay.on('load', function() {
        if (!savedPosition || savedPosition.length !== 4) {
            // Solo intentar fitBounds si la imagen tiene límites válidos después de cargarla
            if (this.getBounds().isValid()) {
                map.fitBounds(this.getBounds());
            } else {
                console.warn("Límites de imagen no válidos al cargar, no se pudo ajustar el mapa.");
            }
        }
    });
}

document.getElementById('guardar').addEventListener('click', () => {
    const corners = imageOverlay.getCorners();
    const positionToSave = corners.map(latLng => [latLng.lat, latLng.lng]);

    fetch("{{ route('map.update', $mapImage->id) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ position: positionToSave })
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            alert('Posición guardada correctamente ✅');
        } else {
            alert('Error al guardar la posición ❌');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error en la comunicación con el servidor ❌');
    });
});
</script>
@endif

</body>
</html>