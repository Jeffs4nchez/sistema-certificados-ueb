<?php
/**
 * Script para reconstruir las relaciones jerárquicas basadas en presupuesto_items
 * Estructura: Programa → Subprograma → Proyecto → Actividad → Fuente → Ubicación → Item
 */

require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "[INFO] Reconstruyendo relaciones desde presupuesto_items...\n\n";
    
    // 1. Obtener todas las fuentes únicas del presupuesto
    echo "[PASO 1] Procesando Fuentes de Financiamiento...\n";
    $stmt = $db->query('
        SELECT DISTINCT codigog4 as codigo_fuente
        FROM presupuesto_items 
        WHERE codigog4 IS NOT NULL 
        ORDER BY codigog4
    ');
    $fuentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($fuentes as $fuente) {
        $codigoFuente = substr(trim($fuente['codigo_fuente']), -3); // Últimos 3 dígitos
        
        // Obtener la actividad asociada a esta fuente
        // (Asumimos que la actividad se determina por el codigog3)
        $stmt = $db->prepare('
            SELECT DISTINCT codigog3 FROM presupuesto_items 
            WHERE codigog4 = ? LIMIT 1
        ');
        $stmt->execute([$fuente['codigo_fuente']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $codigoActividad = substr(trim($result['codigog3']), -3) ?? '';
        
        // Buscar la actividad en la BD
        $stmt = $db->prepare('SELECT id FROM actividades WHERE codigo = ? LIMIT 1');
        $stmt->execute([$codigoActividad]);
        $actividad = $stmt->fetch(PDO::FETCH_ASSOC);
        $actividadId = $actividad['id'] ?? null;
        
        if ($actividadId) {
            // Verificar si la fuente ya existe
            $stmt = $db->prepare('SELECT id FROM fuentes_financiamiento WHERE codigo = ?');
            $stmt->execute([$codigoFuente]);
            $fuenteExiste = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$fuenteExiste) {
                // Crear la fuente con la relación a actividad
                $stmt = $db->prepare('
                    INSERT INTO fuentes_financiamiento (codigo, descripcion, actividad_id, estado)
                    VALUES (?, ?, ?, \'activo\')
                ');
                $stmt->execute([$codigoFuente, 'Fuente ' . $codigoFuente, $actividadId]);
                echo "  ✓ Fuente creada: $codigoFuente → Actividad: $actividadId\n";
            } else {
                // Actualizar la fuente existente con actividad_id
                $stmt = $db->prepare('UPDATE fuentes_financiamiento SET actividad_id = ? WHERE codigo = ?');
                $stmt->execute([$actividadId, $codigoFuente]);
                echo "  ✓ Fuente actualizada: $codigoFuente → Actividad: $actividadId\n";
            }
        }
    }
    
    // 2. Obtener todas las ubicaciones únicas y vincularlas con fuentes
    echo "\n[PASO 2] Procesando Ubicaciones...\n";
    $stmt = $db->query('
        SELECT DISTINCT codigog4 as codigo_fuente, codigog4 as codigo_ubicacion
        FROM presupuesto_items 
        WHERE codigog4 IS NOT NULL
        ORDER BY codigog4
    ');
    $ubicacionesFuentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($ubicacionesFuentes as $row) {
        $codigoFuente = substr(trim($row['codigo_fuente']), -3);
        $codigoUbicacion = substr(trim($row['codigo_ubicacion']), -4);
        
        // Obtener el ID de la fuente
        $stmt = $db->prepare('SELECT id FROM fuentes_financiamiento WHERE codigo = ?');
        $stmt->execute([$codigoFuente]);
        $fuente = $stmt->fetch(PDO::FETCH_ASSOC);
        $fuenteId = $fuente['id'] ?? null;
        
        if ($fuenteId) {
            // Verificar si la ubicación ya existe
            $stmt = $db->prepare('SELECT id FROM ubicaciones WHERE codigo = ?');
            $stmt->execute([$codigoUbicacion]);
            $ubicacionExiste = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ubicacionExiste) {
                // Crear la ubicación con la relación a fuente
                $stmt = $db->prepare('
                    INSERT INTO ubicaciones (codigo, descripcion, fuente_id, estado)
                    VALUES (?, ?, ?, \'activo\')
                ');
                $stmt->execute([$codigoUbicacion, 'Ubicación ' . $codigoUbicacion, $fuenteId]);
                echo "  ✓ Ubicación creada: $codigoUbicacion → Fuente: $codigoFuente\n";
            } else {
                // Actualizar la ubicación existente con fuente_id
                $stmt = $db->prepare('UPDATE ubicaciones SET fuente_id = ? WHERE codigo = ?');
                $stmt->execute([$fuenteId, $codigoUbicacion]);
                echo "  ✓ Ubicación actualizada: $codigoUbicacion → Fuente: $codigoFuente\n";
            }
        }
    }
    
    // 3. Verificar que los items estén vinculados con ubicaciones
    echo "\n[PASO 3] Vinculando Items con Ubicaciones...\n";
    $stmt = $db->query('
        SELECT DISTINCT i.id, i.codigo, pi.codigog4 
        FROM items i
        INNER JOIN presupuesto_items pi ON i.codigo = pi.codigog5
        WHERE i.ubicacion_id IS NULL OR i.ubicacion_id = 0
    ');
    $itemsSinUbicacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($itemsSinUbicacion)) {
        foreach ($itemsSinUbicacion as $item) {
            $codigoUbicacion = substr(trim($item['codigog4']), -4);
            
            // Obtener el ID de la ubicación
            $stmt = $db->prepare('SELECT id FROM ubicaciones WHERE codigo = ?');
            $stmt->execute([$codigoUbicacion]);
            $ubicacion = $stmt->fetch(PDO::FETCH_ASSOC);
            $ubicacionId = $ubicacion['id'] ?? null;
            
            if ($ubicacionId) {
                $stmt = $db->prepare('UPDATE items SET ubicacion_id = ? WHERE id = ?');
                $stmt->execute([$ubicacionId, $item['id']]);
                echo "  ✓ Item vinculado: {$item['codigo']} → Ubicación: $codigoUbicacion\n";
            }
        }
    } else {
        echo "  ✓ Todos los items ya tienen ubicación\n";
    }
    
    echo "\n[SUCCESS] Relaciones reconstruidas correctamente\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
