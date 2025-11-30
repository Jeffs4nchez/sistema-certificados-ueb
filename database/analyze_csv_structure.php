<?php
require_once __DIR__ . '/../app/Database.php';

$csvFile = __DIR__ . '/items.csv';
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ';');

$data = [];
while (($row = fgetcsv($handle, 0, ';')) !== false) {
    if (count($row) < count($header)) continue;
    $record = array_combine($header, $row);
    $key = trim($record['C. ACTIV']) . '|' . 
           trim($record['C. FUENTE DE FINANCIAMIENTO']) . '|' . 
           trim($record['C. UBICACIÓN GEOGRAFICA']);
    $data[$key] = $record;
}
fclose($handle);

echo "ANÁLISIS DEL CSV\n";
echo str_repeat("=", 80) . "\n\n";

$actividades = [];
$fuentes = [];
$ubicaciones = [];

foreach ($data as $record) {
    $act = trim($record['C. ACTIV']);
    $fue = trim($record['C. FUENTE DE FINANCIAMIENTO']);
    $ubi = trim($record['C. UBICACIÓN GEOGRAFICA']);
    
    if (!isset($actividades[$act])) {
        $actividades[$act] = [];
    }
    if (!isset($actividades[$act][$fue])) {
        $actividades[$act][$fue] = [];
    }
    $actividades[$act][$fue][$ubi] = true;
    
    if (!isset($fuentes[$fue])) {
        $fuentes[$fue] = trim($record['D. FUENTE DE FINANCIAMIENTO']);
    }
    if (!isset($ubicaciones[$ubi])) {
        $ubicaciones[$ubi] = trim($record['D. UBICACIÓN GEOGRAFICA']);
    }
}

echo "ACTIVIDADES ENCONTRADAS:\n";
foreach ($actividades as $act => $ftes) {
    echo "\nActividad: $act\n";
    foreach ($ftes as $fue => $ubis) {
        echo "  └─ Fuente: $fue (" . $fuentes[$fue] . ")\n";
        foreach ($ubis as $ubi => $val) {
            echo "     └─ Ubicación: $ubi (" . $ubicaciones[$ubi] . ")\n";
        }
    }
}

echo "\n\nRESUMEN:\n";
echo str_repeat("-", 80) . "\n";
echo "Total Actividades: " . count($actividades) . "\n";
echo "Total Fuentes: " . count($fuentes) . " → " . implode(", ", array_keys($fuentes)) . "\n";
echo "Total Ubicaciones: " . count($ubicaciones) . " → " . implode(", ", array_keys($ubicaciones)) . "\n";
echo "Total combinaciones Actividad-Fuente-Ubicación: " . count($data) . "\n";
?>
