<?php
    /**
     * @file api.php
     * @author  Denis Hošek
     * @date 2024-06-05
     * @brief API pro získání GeoJSON dat z PostgreSQL databáze
     **/
    
    // Nastavení hlavičky pro JSON výstup
    header('Content-Type: application/json');

    // Připojení k databázi PostgreSQL
    $host = 'db';
    $db = 'viagem';
    $user = 'viagem';
    $pass = 'secret';
    $data_source_name = "pgsql:host=$host;dbname=$db";

    // Test připojení k databazy
    try {
        $php_data_object = new PDO($data_source_name, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die("Chyba připojení: ". $e->getMessage());
    }

    // Kontrola, zda je požadavek typu GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode(["error" => "Povoleny jsou pouze GET požadavky."]);
        http_response_code(405);
        exit;
    }
    
    // Povolené hodnoty parametru view
    $allowedViews = ['parcely_komplet', 'parcely', 'parcely_kn_def'];
    $view = $_GET['view'] ?? 'parcely_komplet';
    $searchValue = $_GET['search'] ?? '';

    // Kontrola, zda je hodnota parametru view povolená
    if (!in_array($view, $allowedViews, true)) {
        echo json_encode(["error" => "Neplatný parametr view."]);
        http_response_code(400);
        exit;
    }

    // SQL dotaz pro získání GeoJSON dat z vybrané view
    try {
        if ($searchValue !== '') {
            $sql = "SELECT json_build_object(
                'type', 'FeatureCollection', 
                'features', COALESCE(json_agg(json_build_object(
                    'type', 'Feature', 
                    'geometry', ST_AsGeoJSON(ST_Transform(geom, 4326))::json,
                    'properties', to_jsonb(t) - 'geom'
                )), '[]'::json)
            ) AS geojs FROM $view AS t WHERE t.cislo_parcely ILIKE :search";

            $stmt = $php_data_object->prepare($sql);

            $stmt->execute(['search' => $searchValue . '%']);
        } else {

            $sql = "SELECT json_build_object(
                'type', 'FeatureCollection', 
                'features', COALESCE(json_agg(json_build_object(
                    'type', 'Feature', 
                    'geometry', ST_AsGeoJSON(ST_Transform(geom, 4326))::json,
                    'properties', to_jsonb(t) - 'geom'
                )), '[]'::json)
            ) AS geojs FROM $view AS t";

            $stmt = $php_data_object->prepare($sql);
            $stmt->execute();
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // Povoleni stahovaní APIS
        $isDownload = isset($_GET['download']) && $_GET['download'] == true;
        if ($isDownload) {
        header('Content-Disposition: attachment; filename="parcely_export.geojson"');
        }
        echo $result['geojs'];

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Chyba při dotazu: " . $e->getMessage()]);
    }