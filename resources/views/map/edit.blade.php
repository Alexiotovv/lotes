@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 600px; }
</style>
@endsection
@section('content')
    


<h3 style="text-align:center">🗺️ Editor de Imagen del Mapa</h3>

<form action="{{ route('map.store') }}" method="POST" enctype="multipart/form-data" style="text-align:center; margin-bottom:10px;">
  @csrf
  <input type="file" name="image" required>
  <button type="submit">{{ $mapImage ? 'Reemplazar Imagen' : 'Subir Imagen' }}</button>
</form>

@if($mapImage)
  <div id="map"></div>
  <button id="guardar">💾 Guardar posición</button>
@endif

@endsection


@section('scripts')


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

@endsection