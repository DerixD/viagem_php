<?php
    $host = 'db';
    $db = 'viagem';
    $user = 'viagem';
    $pass = 'secret';
    $data_source_name = "pgsql:host=$host;dbname=$db";

    try {
        $php_data_object = new PDO($data_source_name, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "Připojení k databázi proběhlo úspěšně.\n";
    } catch (PDOException $e) {
        die("Chyba připojení: ". $e->getMessage());
    }

    try {
        $php_data_object->exec(
            "CREATE EXTENSION IF NOT EXISTS postgis"
        );
        echo "PostGIS je aktivní.\n";
    } catch (PDOException $e) {
        die("Chyba při aktivaci PostGIS: " . $e->getMessage());
    }

    $slozky = glob("bin/*", GLOB_ONLYDIR);

    if (empty($slozky)) {
        die("Nebyly nalezeny žádné složky k importu!\n");
    }

    foreach ($slozky as $index => $slozka) {
        echo "\n----------------------------------------\n";
        echo "Zpracovávám katastr: $slozka\n";
    

        $polygonShp = "/var/www/html/$slozka/PARCELY_KN_P.shp";
        $defShp     = "/var/www/html/$slozka/PARCELY_KN_DEF.shp";
        $spedcification = "/var/www/html/$slozka/VB_P.shp";

        echo "$polygonShp $defShp $spedcification";
        if (!file_exists($polygonShp) || !file_exists($defShp) || !file_exists($spedcification)) {
            echo "Chybí některý ze SHP souborů ve složce $slozka/, přeskakuji...\n";
            continue;
        }
        $mod = ($index === 0) ? "-overwrite" : "-append";

        $ogrVersion = shell_exec('ogr2ogr --version 2>&1');

        if ($ogrVersion === null || trim($ogrVersion) === '') {
            die("GDAL není dostupný.");
        }

        $PG = "host=$host dbname=$db user=$user password=$pass";

        $ogr2ogrCMD = 'ogr2ogr -f PostgreSQL ' . 
                $mod . ' ' .
                escapeshellarg("PG:$PG") .
                ' -nln parcely ' .
                escapeshellarg($polygonShp);

        exec($ogr2ogrCMD . ' 2>&1', $output, $returnCode);

        echo "Příkaz:\n" . $ogr2ogrCMD . "\n\n";

        echo "Výstup GDAL:\n";
        echo implode("\n", $output) . "\n";

        echo "Návratový kód: " . $returnCode . "\n";

        if ($returnCode !== 0) {
            die("Import se nezdařil.\n");
        } else {
            echo "Import proběhl úspěšně.\n";
        }

        $ogr2ogrCMD2 = 'ogr2ogr -f PostgreSQL ' . 
                    $mod . ' ' .
                    escapeshellarg("PG:$PG") .
                    ' -nln parcely_kn_def ' .
                    escapeshellarg($defShp);
        
        exec($ogr2ogrCMD2 . ' 2>&1', $output2, $returnCode2);

        echo "Příkaz:\n" . $ogr2ogrCMD2 . "\n\n";

        echo "Výstup GDAL:\n";
        echo implode("\n", $output2) . "\n";

        echo "Návratový kód: " . $returnCode2 . "\n";

        if ($returnCode2 !== 0) {
            echo "Import se nezdařil.\n";
        } else {
            echo "Import proběhl úspěšně.\n";
        }
    }
        $createViewSQL = "
        CREATE OR REPLACE VIEW parcely_komplet AS 
        SELECT 
            p.id_2,
            p.wkb_geometry AS geom,
            d.text_km AS cislo_parcely,
            d.par_vymera AS vymera,
            d.drupoz_kod AS druh_pozemku_kod
        FROM parcely p
        LEFT JOIN parcely_kn_def d ON p.id_2 = d.id_2;";

    try {
        $php_data_object->exec($createViewSQL);
        echo "Pohled 'parcely_komplet' byl vytvořen úspěšně.\n";
    } catch (PDOException $e) {
        die("Chyba při vytváření pohledu: " . $e->getMessage()) . "\n";
    }