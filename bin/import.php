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

    $polygonShp = __DIR__ ."/Jicin/PARCELY_KN_P.shp";
    $defShp = __DIR__ ."/Jicin/PARCELY_KN_DEF.shp";

    if (!file_exists($polygonShp) || !file_exists($defShp)) {
        die("Chybí některý ze SHP souborů ve složce bin/Jicin/!\n");
    }

    $ogrVersion = shell_exec('ogr2ogr --version 2>&1');

    if ($ogrVersion === null || trim($ogrVersion) === '') {
        die("GDAL není dostupný.");
    }

    $PG = "host=$host dbname=$db user=$user password=$pass";

    $ogr2ogrCMD = 'ogr2ogr -f PostgreSQL ' .
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