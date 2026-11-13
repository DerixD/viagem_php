<?php
    /** 
     * @file import.php
     * @author  Denis Hošek
     * @date 2024-06-05
     * @brief Skript pro import dat do databáze
     **/
    
    // Nastavení připojení k databázi
    $host = 'db';
    $db = 'viagem';
    $user = 'viagem';
    $pass = 'secret';
    $data_source_name = "pgsql:host=$host;dbname=$db";

    // Připojení k databázi
    try {
        $php_data_object = new PDO($data_source_name, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "Připojení k databázi proběhlo úspěšně.\n";
    } catch (PDOException $e) {
        die("Chyba připojení: ". $e->getMessage());
    }

    // Aktivace PostGIS rozšíření
    try {
        $php_data_object->exec(
            "CREATE EXTENSION IF NOT EXISTS postgis"
        );
        echo "PostGIS je aktivní.\n";
    } catch (PDOException $e) {
        die("Chyba při aktivaci PostGIS: " . $e->getMessage());
    }

    // Získání seznamu složek v adresáři bin
    $slozky = glob("bin/*", GLOB_ONLYDIR);


    if (empty($slozky)) {
        die("Nebyly nalezeny žádné složky k importu!\n");
    }

    // Iterace přes složky a import dat
    foreach ($slozky as $index => $slozka) {
        echo "\n----------------------------------------\n";
        echo "Zpracovávám katastr: $slozka\n";
    
        // Cesty k SHP souborům
        $polygonShp = "/var/www/html/$slozka/PARCELY_KN_P.shp";
        $defShp     = "/var/www/html/$slozka/PARCELY_KN_DEF.shp";

        echo "$polygonShp $defShp $spedcification";
        if (!file_exists($polygonShp) || !file_exists($defShp)) {
            echo "Chybí některý ze SHP souborů ve složce $slozka/, přeskakuji...\n";
            continue;
        }

        // Určení režimu importu: první složka přepíše tabulku, další přidají data
        $mod = ($index === 0) ? "-overwrite" : "-append";

        // Kontrola dostupnosti GDAL
        $ogrVersion = shell_exec('ogr2ogr --version 2>&1');

        if ($ogrVersion === null || trim($ogrVersion) === '') {
            die("GDAL není dostupný.");
        }

        // Připojení k databázi a import dat
        $PG = "host=$host dbname=$db user=$user password=$pass";

        // Sestavení příkazu pro import dat pomocí GDAL pro tabulku parcely z PARCELY_KN_P.shp
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
        
        // Import dat z PARCELY_KN_DEF.shp do tabulky parcely_kn_def
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

    // Vytvoření pohledu parcely_komplet
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