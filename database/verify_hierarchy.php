<?php
/**
 * Script para verificar la jerarquía completa
 */

require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "[INFO] Verificando jerarquía completa...\n\n";
    
    // 1. Actividades
    echo "[1] ACTIVIDADES:\n";
    $stmt = $db->query('SELECT id, codigo, descripcion FROM actividades ORDER BY id');
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($actividades as $act) {
        echo "  ID: {$act['id']}, Código: {$act['codigo']}, Desc: {$act['descripcion']}\n";
    }
    
    // 2. Fuentes
    echo "\n[2] FUENTES DE FINANCIAMIENTO:\n";
    $stmt = $db->query('SELECT id, codigo, descripcion, actividad_id FROM fuentes_financiamiento ORDER BY id');
    $fuentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($fuentes)) {
        foreach ($fuentes as $fte) {
            echo "  ID: {$fte['id']}, Código: {$fte['codigo']}, Act ID: {$fte['actividad_id']}\n";
        }
    } else {
        echo "  ✗ SIN REGISTROS\n";
    }
    
    // 3. Ubicaciones
    echo "\n[3] UBICACIONES:\n";
    $stmt = $db->query('SELECT id, codigo, descripcion, fuente_id FROM ubicaciones ORDER BY id');
    $ubicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($ubicaciones)) {
        foreach ($ubicaciones as $ubg) {
            echo "  ID: {$ubg['id']}, Código: {$ubg['codigo']}, Fuente ID: {$ubg['fuente_id']}\n";
        }
    } else {
        echo "  ✗ SIN REGISTROS\n";
    }
    
    // 4. Items
    echo "\n[4] ITEMS:\n";
    $stmt = $db->query('SELECT id, codigo, descripcion, ubicacion_id FROM items ORDER BY id LIMIT 10');
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($items)) {
        foreach ($items as $item) {
            echo "  ID: {$item['id']}, Código: {$item['codigo']}, Ubicación ID: {$item['ubicacion_id']}\n";
        }
    } else {
        echo "  ✗ SIN REGISTROS\n";
    }
    
    // 5. Verificar cascada completa desde Actividad 33
    echo "\n[5] CASCADA DESDE ACTIVIDAD 33:\n";
    $stmt = $db->prepare('
        SELECT COUNT(*) as count FROM fuentes_financiamiento 
        WHERE actividad_id = 33
    ');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Fuentes en Actividad 33: {$result['count']}\n";
    
    $stmt = $db->prepare('
        SELECT COUNT(*) as count FROM ubicaciones u
        INNER JOIN fuentes_financiamiento f ON u.fuente_id = f.id
        WHERE f.actividad_id = 33
    ');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Ubicaciones de Actividad 33: {$result['count']}\n";
    
    $stmt = $db->prepare('
        SELECT COUNT(*) as count FROM items i
        INNER JOIN ubicaciones u ON i.ubicacion_id = u.id
        INNER JOIN fuentes_financiamiento f ON u.fuente_id = f.id
        WHERE f.actividad_id = 33
    ');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Items de Actividad 33: {$result['count']}\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
?>
