<?php
    header('Content-Type: application/json');
    $host = 'db';
    $db = 'viagem';
    $user = 'viagem';
    $pass = 'secret';
    $data_source_name = "pgsql:host=$host;dbname=$db";

    try {
        $php_data_object = new PDO($data_source_name, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die("Chyba připojení: ". $e->getMessage());

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode(["error" => "Povoleny jsou pouze GET požadavky."]);
        http_response_code(405);
        exit;
    }
    }

    $allowedViews = ['parcely_komplet', 'parcely', 'parcely_kn_def'];
    $view = $_GET['view'] ?? 'parcely_komplet';

    if (!in_array($view, $allowedViews, true)) {
        echo json_encode(["error" => "Neplatný parametr view."]);
        http_response_code(400);
        exit;
    }

    try {
        $sql = "SELECT json_build_object(
            'type', 'FeatureCollection', 
            'features', COALESCE(json_agg(json_build_object(
                'type', 'Feature', 
                'geometry', ST_AsGeoJSON(ST_Transform(geom, 4326))::json,
                'properties', to_jsonb(t) - 'geom'
            )), '[]'::json)
        ) AS geojs FROM $view AS t;";

        $stmt = $php_data_object->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo $result['geojs'];
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Chyba při dotazu: " . $e_msg = $e->getMessage()]);
    }