<?php
/**
 * Script para importar datos desde CSV y crear relaciones jerárquicas
 * Programa → Subprograma → Proyecto → Actividad → Ubicación → Fuente → Items
 */

require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $csvFile = __DIR__ . '/items.csv';
    
    if (!file_exists($csvFile)) {
        echo "[ERROR] Archivo CSV no encontrado: $csvFile\n";
        exit(1);
    }
    
    echo "IMPORTANDO DATOS DEL CSV\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Limpiar tabla plana (opcional)
    // $db->query('TRUNCATE TABLE estructura_presupuestaria CASCADE');
    
    $handle = fopen($csvFile, 'r');
    if (!$handle) {
        echo "[ERROR] No se pudo abrir el archivo CSV\n";
        exit(1);
    }
    

    // Leer encabezados
    $headers = fgetcsv($handle, 10000, ';');
    echo "[INFO] Encabezados encontrados: " . count($headers) . "\n";
    foreach ($headers as $i => $header) {
        echo "  Col $i: '$header' (normalizado: '" . normalize_header($header) . "')\n";
    }

    // Mapeo de encabezados CSV a campos de la tabla
    // Mapeo flexible: normaliza encabezados para aceptar variantes
    function normalize_header($header) {
        $header = strtolower(trim($header));
        $header = str_replace(['á','é','í','ó','ú','ñ'],['a','e','i','o','u','n'],$header);
        $header = preg_replace('/[^a-z0-9\.]/','', $header); // quita espacios y caracteres especiales
        return $header;
    }
    $map = [
        'c.programa' => 'cod_programa',
        'd.programa' => 'desc_programa',
        'c.subprograma' => 'cod_subprograma',
        'd.subprograma' => 'desc_subprograma',
        'c.subprog' => 'cod_subprograma',
        'd.subprog' => 'desc_subprograma',
        'c.proyecto' => 'cod_proyecto',
        'd.proyecto' => 'desc_proyecto',
        'c.activ' => 'cod_actividad',
        'd.actividad' => 'desc_actividad',
        'c.item' => 'cod_item',
        'd.item' => 'desc_item',
        'c.ubicaciongeografica' => 'cod_ubicacion',
        'd.ubicaciongeografica' => 'desc_ubicacion',
        'c.ubicacion' => 'cod_ubicacion',
        'd.ubicacion' => 'desc_ubicacion',
        'c.fuentedefinanciamiento' => 'cod_fuente',
        'd.fuentedefinanciamiento' => 'desc_fuente',
        'c.fuenteded' => 'cod_fuente',
        'fuentedec' => 'desc_fuente',
        'organismo' => 'cod_organismo',
        'c.organismo' => 'cod_organismo',
        'd.organismo' => 'desc_organismo',
        'organism' => 'cod_organismo',
        'd.organism' => 'desc_organismo',
        'npest' => 'cod_nprest',
        'n.prest' => 'cod_nprest',
        'descripcion' => 'desc_nprest',
        'd.n.prest' => 'desc_nprest'
    ];

    $rowCount = 0;
    $inserted = 0;

    while (($row = fgetcsv($handle, 10000, ';')) !== FALSE) {
        $rowCount++;
        $data = [];
        foreach ($headers as $i => $header) {
            $norm = normalize_header($header);
            if (isset($map[$norm])) {
                $data[$map[$norm]] = trim($row[$i]);
            }
        }
        // Generar el campo codigo_completo (opcional)
        $data['codigo_completo'] = ($data['cod_programa'] ?? '') . ($data['cod_subprograma'] ?? '') . ($data['cod_proyecto'] ?? '') . ($data['cod_actividad'] ?? '') . ($data['cod_fuente'] ?? '') . ($data['cod_ubicacion'] ?? '') . ($data['cod_item'] ?? '');

        // Mostrar datos mapeados para depuración
        echo "[ROW $rowCount] Datos mapeados: ";
        print_r($data);

        // Insertar en la tabla plana solo si hay al menos un código
        if (!empty($data['cod_programa'])) {
            $fields = array_keys($data);
            $placeholders = array_map(function($f) { return ':' . $f; }, $fields);
            $sql = 'INSERT INTO estructura_presupuestaria (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')';
            $stmt = $db->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            if ($stmt->execute()) {
                $inserted++;
            } else {
                echo "[ERROR] No se pudo insertar la fila $rowCount: ";
                print_r($stmt->errorInfo());
            }
        } else {
            echo "[SKIP] Fila $rowCount ignorada por falta de cod_programa\n";
        }
        if ($rowCount % 50 == 0) {
            echo "[PROGRESS] Procesadas $rowCount filas...\n";
        }
    }

    fclose($handle);

    echo "\n[RESUMEN]\n";
    echo str_repeat("=", 80) . "\n";
    echo "Total filas procesadas: $rowCount\n";
    echo "Filas insertadas en estructura_presupuestaria: $inserted\n";

    echo "\n✓ Importación completada\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
