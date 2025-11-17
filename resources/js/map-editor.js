// resources/js/map-editor.js

// ✅ Importar dependencias
import * as L from 'leaflet';

// ✅ Importar Leaflet Draw (requerido por distortableimage)
// import '@fortawesome/fontawesome-free/js/all.min.js'; // Opcional: si usa íconos
import 'leaflet-draw';

// ✅ Importar Distortable Image
import 'leaflet-distortableimage';

// ✅ Asegurar que L.EditAction esté disponible
// Esto se hace automáticamente al importar leaflet-draw antes que distortableimage

// ✅ Función para inicializar el editor de mapas
window.initMapEditor = function (mapId, imageUrl, initialPosition = null) {
    // ✅ Verificar que todo esté disponible
    if (typeof L.distortableImageOverlay === 'undefined') {
        console.error('❌ Leaflet DistortableImage no está disponible');
        return null;
    }

    // ✅ Crear mapa
    const map = L.map(mapId).setView([-3.844051, -73.3432986], 19);

    // ✅ Añadir capa base
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 21,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // ✅ Opciones del overlay
    const options = {
        editable: true,
        // Opcional: definir esquinas iniciales si se tiene posición guardada
        ...(initialPosition && Array.isArray(initialPosition) && initialPosition.length === 4 && {
            corners: initialPosition.map(([lat, lng]) => L.latLng(lat, lng))
        })
    };

    // ✅ Añadir imagen distorsionable
    const imageOverlay = L.distortableImageOverlay(imageUrl, options).addTo(map);

    // ✅ Devolver objetos para que el código externo pueda interactuar
    return { map, imageOverlay };
};

// ✅ Opcional: también exportar como módulo
export { initMapEditor };