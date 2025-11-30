<?php
/**
 * Analizar estructura del CSV para verificar relaciones
 * 1 Actividad → muchas Fuentes → muchas Ubicaciones → muchos Items
 */

$csvFile = 'c:/Users/jeffo/OneDrive/Escritorio/Programa Financiero certificaciones/items.csv';

if (!file_exists($csvFile)) {
    echo "Archivo no encontrado: $csvFile\n";
    exit(1);
}

echo "Analizando CSV: items.csv\n";
echo "==========================\n\n";

$data = [];
$header = null;
$row_num = 0;

if (($handle = fopen($csvFile, 'r')) !== FALSE) {
    while (($row = fgetcsv($handle, 10000, ';')) !== FALSE) {
        $row_num++;
        
        if ($row_num == 1) {
            $header = $row;
            continue;
        }
        
        // Crear índice asociativo
        $record = array_combine($header, $row);
        
        $programa = trim($record['C. Programa'] ?? '');
        $subprograma = trim($record['C. SUB PROGRAMA'] ?? '');
        $proyecto = trim($record['C. PROYECTO'] ?? '');
        $actividad = trim($record['C. ACTIV'] ?? '');
        $item = trim($record['C. ITEM'] ?? '');
        $ubicacion = trim($record['C. UBICACIÓN GEOGRAFICA'] ?? '');
        $fuente = trim($record['C. FUENTE DE FINANCIAMIENTO'] ?? '');
        
        $key = "$programa|$subprograma|$proyecto|$actividad";
        
        if (!isset($data[$key])) {
            $data[$key] = [
                'programa' => $programa,
                'desc_programa' => trim($record['D. Programa'] ?? ''),
                'subprograma' => $subprograma,
                'desc_subprograma' => trim($record['D. SUB PROGRAMA'] ?? ''),
                'proyecto' => $proyecto,
                'desc_proyecto' => trim($record['D. PROYECTO'] ?? ''),
                'actividad' => $actividad,
                'desc_actividad' => trim($record['D. ACTIVIDAD'] ?? ''),
                'fuentes' => [],
                'ubicaciones' => [],
                'items' => []
            ];
        }
        
        // Agregar fuente única
        if (!in_array($fuente, $data[$key]['fuentes'])) {
            $data[$key]['fuentes'][] = [
                'codigo' => $fuente,
                'descripcion' => trim($record['D. FUENTE DE FINANCIAMIENTO'] ?? '')
            ];
        }
        
        // Agregar ubicación única
        if (!in_array($ubicacion, array_column($data[$key]['ubicaciones'], 'codigo'))) {
            $data[$key]['ubicaciones'][] = [
                'codigo' => $ubicacion,
                'descripcion' => trim($record['D. UBICACIÓN GEOGRAFICA'] ?? '')
            ];
        }
        
        // Agregar item único
        if (!in_array($item, array_column($data[$key]['items'], 'codigo'))) {
            $data[$key]['items'][] = [
                'codigo' => $item,
                'descripcion' => trim($record['D. ITEM'] ?? ''),
                'ubicacion' => $ubicacion,
                'fuente' => $fuente
            ];
        }
    }
    fclose($handle);
}

// Mostrar análisis
foreach ($data as $key => $actividad_data) {
    echo "═══════════════════════════════════════════════════════════\n";
    echo "ACTIVIDAD: {$actividad_data['actividad']} - {$actividad_data['desc_actividad']}\n";
    echo "Programa: {$actividad_data['programa']} - {$actividad_data['desc_programa']}\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "📍 FUENTES (" . count($actividad_data['fuentes']) . "):\n";
    foreach ($actividad_data['fuentes'] as $f) {
        echo "   • {$f['codigo']}: {$f['descripcion']}\n";
    }
    
    echo "\n📍 UBICACIONES (" . count($actividad_data['ubicaciones']) . "):\n";
    foreach ($actividad_data['ubicaciones'] as $u) {
        echo "   • {$u['codigo']}: {$u['descripcion']}\n";
    }
    
    echo "\n📍 ITEMS (" . count($actividad_data['items']) . "):\n";
    foreach ($actividad_data['items'] as $i) {
        echo "   • {$i['codigo']}: {$i['descripcion']}\n";
        echo "      └─ Ubicación: {$i['ubicacion']}, Fuente: {$i['fuente']}\n";
    }
    
    // Verificar relaciones
    echo "\n✓ VERIFICACIÓN DE RELACIONES:\n";
    
    // Verificar que cada fuente tenga ubicaciones
    $fuentes_con_ubicaciones = [];
    foreach ($actividad_data['items'] as $item) {
        $fuentes_con_ubicaciones[$item['fuente']][] = $item['ubicacion'];
    }
    
    foreach ($fuentes_con_ubicaciones as $fuente => $ubicaciones) {
        $ubicaciones_unicas = array_unique($ubicaciones);
        echo "   • Fuente $fuente → " . count($ubicaciones_unicas) . " ubicaciones\n";
    }
    
    echo "\n";
}

echo "\n📊 RESUMEN:\n";
echo "Total de Actividades: " . count($data) . "\n";
$total_fuentes = 0;
$total_ubicaciones = 0;
$total_items = 0;

foreach ($data as $act) {
    $total_fuentes += count($act['fuentes']);
    $total_ubicaciones += count($act['ubicaciones']);
    $total_items += count($act['items']);
}

echo "Total de Fuentes únicas: $total_fuentes\n";
echo "Total de Ubicaciones únicas: $total_ubicaciones\n";
echo "Total de Items únicos: $total_items\n";
?>
