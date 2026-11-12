<!DOCTYPE html>
<html lang='en'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <title>Viagem php projekt</title>
        <link rel='stylesheet' href='./style/map.css'>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>

    </head>
    <body>
        
        <div id='map'></div>

        <script>
            const map = L.map('map').setView([50.437, 15.351], 13);
            
            // Opraveno titleLayer -> tileLayer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            fetch('./api.php') 
            .then(response => response.json())
            .then(data => {
                L.geoJson(data, {
                    style: {
                        color: '#ff7800',
                        weight: 2,
                        fillOpacity: 0.2
                    },
                    onEachFeature: function (feature, layer) {
                        if (feature.properties) {
                            let popupContent = "Parcela: " + (feature.properties.parcislo || 'N/A');
                            layer.bindPopup(popupContent);
                        }
                    }
                }).addTo(map);
            })
            .catch(error => console.error('Chyba při načítání dat z API:', error));
        </script>
    </body>
</html>