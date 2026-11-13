<?php
/**
 * @file index.php
 * @author  Denis Hošek
 * @date 2024-06-05
 * @brief Frontendová část s mapou (Leaflet.js) a načítáním GeoJSON z API
 **/?>
<!DOCTYPE html>
<html lang='en'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <title>Viagem php projekt</title>
        <!-- Přidání stylů pro mapu a navbar -->
        <link rel='stylesheet' href='./style/main.css'>
        <!-- Přidání Leaflet.js knihovny -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
        <!-- Přidání Bootstrap 5.3.9 knihovny -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
    </head>
    <body>
        <!-- Navigační lišta -->
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Viagem Katastr</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Vyberte město...
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item locality-link" href="#" data-lat="50.4193" data-lng="15.3470">Čejkovice</a></li>
                            <li><a class="dropdown-item locality-link" href="#" data-lat="50.4516" data-lng="15.3207">Holín</a></li>
                            <li><a class="dropdown-item locality-link" href="#" data-lat="50.4550" data-lng="15.3508">Kbelnice</a></li>
                            <li><a class="dropdown-item locality-link" href="#" data-lat="50.4262" data-lng="15.3788">Moravčice</a></li>
                            <li><a class="dropdown-item locality-link" href="#" data-lat="50.4145" data-lng="15.3728">Popovice</a></li>
                            <li><a class="dropdown-item locality-link" href="#" data-lat="50.4322" data-lng="15.3989">Robousy</a></li>
                            <li><a class="dropdown-item locality-link" href="#" data-lat="50.4550" data-lng="15.3848">Valdice</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item locality-link" href="#" data-lat="50.4372" data-lng="15.3516">Jičín</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Vyhledat parcelu" aria-label="Vyhledat parcelu"/>
                    <button class="btn btn-outline-success search-btn" type="submit">vyhledat</button>
                    <button class="btn btn-outline-success download-btn" type="button">stáhnout</button>
                </form>
                </div>
            </div>
        </nav>

        <!-- Zobrazení mapy -->
        <div id='map'></div>

        <script>
            // Inicializace mapy
            const map = L.map('map').setView([50.437, 15.351], 13);

            // Odstranění tabindex atributu z kontejneru mapy, pokud existuje
            if (map.getContainer().hasAttribute('tabindex')) {
                map.getContainer().removeAttribute('tabindex');
        }
            // Přidání OpenStreetMap dlaždicD
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Načtení dat z API a přidání do mapy
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
                            let popupContent = "<h3>Parcela: " + (feature.properties.cislo_parcely || 'N/A') + "</h3>"+ "<b>Výměra: </b>" + (feature.properties.vymera || 'N/A') +" m² <br>"+ "<b>Druh pozemku: </b>" + (feature.properties.druh_pozemku_kod || 'N/A');
                            layer.bindPopup(popupContent);
                        }
                    }
                }).addTo(map);
            })
            .catch(error => console.error('Chyba při načítání dat z API:', error));

            // Přidání události pro odkazy v navigační liště
            document.querySelectorAll('.locality-link').forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const lat = parseFloat(this.getAttribute('data-lat'));
                    const lng = parseFloat(this.getAttribute('data-lng'));
                    const zoom = parseInt(this.getAttribute('data-zoom')) || 14;

                    if (!isNaN(lat) && !isNaN(lng)) {
                        map.setView([lat, lng], zoom);
                    }
                });
            });

            // Přidání události pro vyhledávací formulář
            const searchForm = document.querySelector('.search-btn').closest('form');

            if (searchForm) {
                searchForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const searchInput = this.querySelector('input[type="search"]');
                    const query = searchInput.value.trim() || '';

                    const url = "./api.php?search=" + encodeURIComponent(query);

                    fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // Odstranění předchozích vrstev z mapy
                        map.eachLayer(layer => {
                            if (layer instanceof L.GeoJSON) {
                                map.removeLayer(layer);
                            }
                        });

                        // Přidání nové vrstvy s výsledky vyhledávání
                        L.geoJson(data, {
                            style: {
                                color: '#ff7800',
                                weight: 2,
                                fillOpacity: 0.2
                            },
                            onEachFeature: function (feature, layer) {
                                if (feature.properties) {
                                    let popupContent = "<h3>Parcela: " + (feature.properties.cislo_parcely || 'N/A') + "</h3>"+ "<b>Výměra: </b>" + (feature.properties.vymera || 'N/A') +" m² <br>"+ "<b>Druh pozemku: </b>" + (feature.properties.druh_pozemku_kod || 'N/A');
                                    layer.bindPopup(popupContent);
                                }
                            }
                        }).addTo(map);       
                    })
                });
            }

            const downloadBtn = document.querySelector('.download-btn');

            if (downloadBtn) {
                downloadBtn.addEventListener('click', function(event) {
                    event.preventDefault();

                    const searchInput = this.closest('form').querySelector('input[type="search"]');
                    const query = searchInput ? searchInput.value.trim() : '';

                    const url = "./api.php?search=" + encodeURIComponent(query) + "&download=true";

                    fetch(url)
                    .then(response => response.blob())
                    .then(blob => {
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');

                        a.href = downloadUrl;
                        a.download = 'parcely_export.geojson';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(downloadUrl);
                    })
                    .catch(error => console.error('Chyba při stahování souboru:', error));
                });
            }
        </script>
    </body>
</html>