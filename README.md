# Viagem Katastr – GIS webová aplikace

Webová aplikace pro zobrazení katastrálních území v okolí Jičína. 

---
## Funkce
- **Interaktivní virtualizace mapy:** Založená na knihovně Leaflet.js
- **Dynamické vyhledávání:** Dohledávání parcel s automatickým mazáním a aktualizací mapových vrstev.
- **Export do GeoJSONu:** Možnost exportu aktuálního pohledu mapy ve formátu GeoJSON.
- **Databáze:** Backendové zpracování s využitím PHP, PDO a PostGIS.
- **Automatizovaná inicializace:** Inicializace prostředí a naplnění databáze pomocí Docker Compose nebo skriptu.

---

## Použité technologie

- **Frontend:** Leaflet.js, Bootstrap 5.3
- **Backend:** PHP, PDO
- **Databáze:** PostgreSQL s rozšířením PostGIS
- **Prostředí a orchestrace:** Docker & Docker Compose
- **Programátorská dokumentace:** Doxygen

---
## První spuštění

### Pomocí bat souboru (Pro Windows)
- **Spuštěním souboru:** start.bat
- **V cmd příkazem:** ./start.bat

### Pomocí sh souboru (pro Linux)
- **Spuštěním souboru:** start.sh
- **V cmd příkazem:** ./start.sh

### Manuálně
Příkazy:
    - **Pro inicializaci prostředí:** docker compose up -d --build
    - **Pro zpracování dat:** docker compose exec php php ./bin/import.php

Vložené do terminálu/cmd v tomto pořadí