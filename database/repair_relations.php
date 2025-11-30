<?php
/**
 * Script para reparar relaciones rotas en la BD
 * Problemas identificados:
 * 1. Fuente 001 sin ubicaciones
 * 2. Ubicaciones huérfanas sin fuente
 */

require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "REPARACIÓN DE RELACIONES\n";
    echo "=======================\n\n";
    
    // 1. Encontrar ubicaciones sin fuente
    echo "[PASO 1] Identificando ubicaciones huérfanas...\n";
    $stmt = $db->query('
        SELECT u.id, u.codigo, u.descripcion
        FROM ubicaciones u
        WHERE u.fuente_id IS NULL
    ');
    $ubicacionesHuerfanas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($ubicacionesHuerfanas)) {
        echo "  Ubicaciones sin fuente: " . count($ubicacionesHuerfanas) . "\n";
        foreach ($ubicacionesHuerfanas as $ub) {
            echo "    - {$ub['codigo']} ({$ub['descripcion']})\n";
        }
    } else {
        echo "  ✓ No hay ubicaciones huérfanas\n";
    }
    
    // 2. Encontrar fuentes sin ubicaciones
    echo "\n[PASO 2] Identificando fuentes sin ubicaciones...\n";
    $stmt = $db->query('
        SELECT f.id, f.codigo, f.descripcion, a.codigo as actividad_codigo
        FROM fuentes_financiamiento f
        JOIN actividades a ON f.actividad_id = a.id
        LEFT JOIN ubicaciones u ON f.id = u.fuente_id
        WHERE u.id IS NULL
        GROUP BY f.id, f.codigo, f.descripcion, a.codigo
    ');
    $fuentesSinUbicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($fuentesSinUbicaciones)) {
        echo "  Fuentes sin ubicaciones: " . count($fuentesSinUbicaciones) . "\n";
        foreach ($fuentesSinUbicaciones as $fte) {
            echo "    - {$fte['codigo']} (Actividad: {$fte['actividad_codigo']})\n";
        }
    } else {
        echo "  ✓ Todas las fuentes tienen ubicaciones\n";
    }
    
    // 3. PASO OMITIDO: Dejar ubicaciones huérfanas como están
    echo "\n[PASO 3] Dejando ubicaciones huérfanas intactas (como se solicitó)...\n";
    echo "  ℹ Ubicaciones huérfanas serán ignoradas\n";
    
    // 4. Reparación: Crear ubicaciones para fuentes sin ubicaciones
    echo "\n[PASO 4] Creando ubicaciones para fuentes sin ubicaciones...\n";
    if (!empty($fuentesSinUbicaciones)) {
        foreach ($fuentesSinUbicaciones as $fte) {
            // Buscar todas las ubicaciones existentes de la actividad
            $stmt = $db->prepare('
                SELECT COUNT(*) as total FROM ubicaciones u
                JOIN fuentes_financiamiento f ON u.fuente_id = f.id
                WHERE f.actividad_id = (
                    SELECT actividad_id FROM fuentes_financiamiento WHERE id = ?
                )
            ');
            $stmt->execute([$fte['id']]);
            $totalUbicaciones = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Crear una ubicación única basada en el número de ubicaciones + 1
            $nuevoCodigoNum = str_pad($totalUbicaciones, 4, '0', STR_PAD_LEFT);
            $nuevoCodigo = '0' . $nuevoCodigoNum;
            
            echo "  Fuente {$fte['codigo']}: Creando ubicación con código {$nuevoCodigo}...\n";
            $stmt = $db->prepare('
                INSERT INTO ubicaciones (codigo, descripcion, estado, fuente_id)
                VALUES (?, ?, ?, ?)
            ');
            try {
                $stmt->execute([
                    $nuevoCodigo,
                    'Ubicación para ' . $fte['descripcion'],
                    'activo',
                    $fte['id']
                ]);
                echo "    ✓ Ubicación creada: {$nuevoCodigo}\n";
            } catch (Exception $e) {
                echo "    ⚠ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // 5. Verificación final
    echo "\n[PASO 5] Verificación final...\n";
    
    $stmt = $db->query('
        SELECT f.id, f.codigo FROM fuentes_financiamiento f
        LEFT JOIN ubicaciones u ON f.id = u.fuente_id
        WHERE u.id IS NULL
        GROUP BY f.id, f.codigo
    ');
    $fuentesSin = $stmt->fetchAll();
    
    if (empty($fuentesSin)) {
        echo "  ✓ REPARACIÓN COMPLETADA: Todas las fuentes tienen ubicaciones\n";
        echo "  ℹ Ubicaciones huérfanas dejadas intactas por solicitud\n";
    } else {
        echo "  ⚠ Aún hay fuentes sin ubicaciones:\n";
        foreach ($fuentesSin as $f) {
            echo "    - Fuente {$f['codigo']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
