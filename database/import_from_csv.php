<?php
/**
 * Script para importar items.csv e integrar la jerarquía completa
 * Programa → Subprograma → Proyecto → Actividad → Fuente → Ubicación → Items
 */

require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $csvFile = __DIR__ . '/items.csv';
    
    if (!file_exists($csvFile)) {
        echo "[ERROR] Archivo no encontrado: $csvFile\n";
        exit(1);
    }
    
    echo "IMPORTACIÓN DE CSV\n";
    echo str_repeat("=", 80) . "\n\n";
    
    $handle = fopen($csvFile, 'r');
    $header = fgetcsv($handle, 0, ';');
    
    $processed = 0;
    $errors = 0;
    $data = [];
    
    // Leer todos los registros
    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        if (count($row) < count($header)) continue;
        
        $record = array_combine($header, $row);
        
        $key = $record['C. Programa'] . '|' . 
               $record['C. ACTIV'] . '|' . 
               $record['C. FUENTE DE FINANCIAMIENTO'] . '|' . 
               $record['C. UBICACIÓN GEOGRAFICA'];
        
        if (!isset($data[$key])) {
            $data[$key] = $record;
        }
        $processed++;
    }
    fclose($handle);
    
    echo "[INFO] Registros únicos a procesar: " . count($data) . "\n";
    echo "[INFO] Total de líneas CSV: $processed\n\n";
    
    $db->beginTransaction();
    
    $addedPrograms = 0;
    $addedSubprogramas = 0;
    $addedProyectos = 0;
    $addedActividades = 0;
    $addedFuentes = 0;
    $addedUbicaciones = 0;
    $addedItems = 0;
    
    foreach ($data as $record) {
        $programa_cod = trim($record['C. Programa']);
        $programa_desc = trim($record['D. Programa']);
        $subprograma_cod = trim($record['C. SUB PROGRAMA']);
        $subprograma_desc = trim($record['D. SUB PROGRAMA']);
        $proyecto_cod = trim($record['C. PROYECTO']);
        $proyecto_desc = trim($record['D. PROYECTO']);
        $actividad_cod = trim($record['C. ACTIV']);
        $actividad_desc = trim($record['D. ACTIVIDAD']);
        $item_cod = trim($record['C. ITEM']);
        $item_desc = trim($record['D. ITEM']);
        $ubicacion_cod = trim($record['C. UBICACIÓN GEOGRAFICA']);
        $ubicacion_desc = trim($record['D. UBICACIÓN GEOGRAFICA']);
        $fuente_cod = trim($record['C. FUENTE DE FINANCIAMIENTO']);
        $fuente_desc = trim($record['D. FUENTE DE FINANCIAMIENTO']);
        
        // 1. PROGRAMA
        $stmt = $db->prepare('SELECT id FROM programas WHERE codigo = ?');
        $stmt->execute([$programa_cod]);
        $prog = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$prog) {
            $stmt = $db->prepare('INSERT INTO programas (codigo, descripcion, estado) VALUES (?, ?, ?) RETURNING id');
            $stmt->execute([$programa_cod, $programa_desc, 'activo']);
            $programa_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $addedPrograms++;
        } else {
            $programa_id = $prog['id'];
        }
        
        // 2. SUBPROGRAMA
        $stmt = $db->prepare('SELECT id FROM subprogramas WHERE codigo = ? AND programa_id = ?');
        $stmt->execute([$subprograma_cod, $programa_id]);
        $subprog = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subprog) {
            $stmt = $db->prepare('INSERT INTO subprogramas (codigo, descripcion, programa_id, estado) VALUES (?, ?, ?, ?) RETURNING id');
            $stmt->execute([$subprograma_cod, $subprograma_desc, $programa_id, 'activo']);
            $subprograma_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $addedSubprogramas++;
        } else {
            $subprograma_id = $subprog['id'];
        }
        
        // 3. PROYECTO
        $stmt = $db->prepare('SELECT id FROM proyectos WHERE codigo = ? AND subprograma_id = ?');
        $stmt->execute([$proyecto_cod, $subprograma_id]);
        $proy = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$proy) {
            $stmt = $db->prepare('INSERT INTO proyectos (codigo, descripcion, subprograma_id, estado) VALUES (?, ?, ?, ?) RETURNING id');
            $stmt->execute([$proyecto_cod, $proyecto_desc, $subprograma_id, 'activo']);
            $proyecto_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $addedProyectos++;
        } else {
            $proyecto_id = $proy['id'];
        }
        
        // 4. ACTIVIDAD
        $stmt = $db->prepare('SELECT id FROM actividades WHERE codigo = ? AND proyecto_id = ?');
        $stmt->execute([$actividad_cod, $proyecto_id]);
        $activ = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$activ) {
            $stmt = $db->prepare('INSERT INTO actividades (codigo, descripcion, proyecto_id, estado) VALUES (?, ?, ?, ?) RETURNING id');
            $stmt->execute([$actividad_cod, $actividad_desc, $proyecto_id, 'activo']);
            $actividad_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $addedActividades++;
        } else {
            $actividad_id = $activ['id'];
        }
        
        // 5. FUENTE
        $stmt = $db->prepare('SELECT id FROM fuentes_financiamiento WHERE codigo = ? AND actividad_id = ?');
        $stmt->execute([$fuente_cod, $actividad_id]);
        $fuente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$fuente) {
            $stmt = $db->prepare('INSERT INTO fuentes_financiamiento (codigo, descripcion, actividad_id, estado) VALUES (?, ?, ?, ?) RETURNING id');
            $stmt->execute([$fuente_cod, $fuente_desc, $actividad_id, 'activo']);
            $fuente_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $addedFuentes++;
        } else {
            $fuente_id = $fuente['id'];
        }
        
        // 6. UBICACIÓN
        $stmt = $db->prepare('SELECT id FROM ubicaciones WHERE codigo = ? AND fuente_id = ?');
        $stmt->execute([$ubicacion_cod, $fuente_id]);
        $ubicacion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ubicacion) {
            $stmt = $db->prepare('INSERT INTO ubicaciones (codigo, descripcion, fuente_id, estado) VALUES (?, ?, ?, ?) RETURNING id');
            $stmt->execute([$ubicacion_cod, $ubicacion_desc, $fuente_id, 'activo']);
            $ubicacion_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            $addedUbicaciones++;
        } else {
            $ubicacion_id = $ubicacion['id'];
        }
        
        // 7. ITEM
        $stmt = $db->prepare('SELECT id FROM items WHERE codigo = ? AND ubicacion_id = ?');
        $stmt->execute([$item_cod, $ubicacion_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            $stmt = $db->prepare('INSERT INTO items (codigo, descripcion, ubicacion_id, estado) VALUES (?, ?, ?, ?) RETURNING id');
            $stmt->execute([$item_cod, $item_desc, $ubicacion_id, 'activo']);
            $addedItems++;
        }
    }
    
    $db->commit();
    
    echo "\n[RESUMEN DE IMPORTACIÓN]\n";
    echo str_repeat("-", 80) . "\n";
    echo "✓ Programas creados: $addedPrograms\n";
    echo "✓ Subprogramas creados: $addedSubprogramas\n";
    echo "✓ Proyectos creados: $addedProyectos\n";
    echo "✓ Actividades creadas: $addedActividades\n";
    echo "✓ Fuentes creadas: $addedFuentes\n";
    echo "✓ Ubicaciones creadas: $addedUbicaciones\n";
    echo "✓ Items creados: $addedItems\n";
    echo "\n[IMPORTACIÓN COMPLETADA]\n";
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
