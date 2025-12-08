<?php
/**
 * CORREGIR cantidad_pendiente e implementar col4 correctamente
 * 
 * REGLA: cantidad_pendiente = monto - cantidad_liquidacion
 * REGLA: col4 en presupuesto = suma de montos de items certificados
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "CORRECCIÓN: Reparar cantidad_pendiente y col4\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // PASO 1: Corregir cantidad_pendiente en detalle_certificados
    echo "1️⃣  CORRIGIENDO cantidad_pendiente\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT id, monto, cantidad_liquidacion, cantidad_pendiente
        FROM detalle_certificados
        ORDER BY id
    ");
    
    $items = $stmt->fetchAll();
    $corregidas = 0;
    
    foreach ($items as $item) {
        $pendiente_correcto = $item['monto'] - $item['cantidad_liquidacion'];
        
        if ($pendiente_correcto != $item['cantidad_pendiente']) {
            echo "Item ID {$item['id']}:\n";
            echo "  ANTES: Monto {$item['monto']} - Liquidado {$item['cantidad_liquidacion']} = Pendiente {$item['cantidad_pendiente']}\n";
            
            $stmt_upd = $db->prepare("
                UPDATE detalle_certificados 
                SET cantidad_pendiente = ? 
                WHERE id = ?
            ");
            $stmt_upd->execute([$pendiente_correcto, $item['id']]);
            
            echo "  AHORA: Monto {$item['monto']} - Liquidado {$item['cantidad_liquidacion']} = Pendiente {$pendiente_correcto}\n";
            $corregidas++;
            echo "\n";
        }
    }
    
    echo "✅ Se corrigieron $corregidas items\n\n";
    
    // PASO 2: Corregir col4 en presupuesto_items
    echo "2️⃣  CORRIGIENDO col4 EN PRESUPUESTO\n";
    echo str_repeat("-", 80) . "\n";
    
    // Obtener suma de montos por código_completo
    $stmt = $db->query("
        SELECT codigo_completo, SUM(monto) as total_monto
        FROM detalle_certificados
        WHERE codigo_completo IS NOT NULL
        GROUP BY codigo_completo
    ");
    
    $codigos = $stmt->fetchAll();
    $corregidas_presupuesto = 0;
    
    foreach ($codigos as $codigo) {
        $sql = "
            SELECT id, col4 
            FROM presupuesto_items 
            WHERE codigo_completo = ?
        ";
        
        $stmt_check = $db->prepare($sql);
        $stmt_check->execute([$codigo['codigo_completo']]);
        $presupuesto = $stmt_check->fetch();
        
        if ($presupuesto) {
            echo "Código: {$codigo['codigo_completo']}\n";
            echo "  Col4 ANTES: " . number_format($presupuesto['col4'], 2) . "\n";
            
            $stmt_upd = $db->prepare("
                UPDATE presupuesto_items 
                SET col4 = ? 
                WHERE id = ?
            ");
            $stmt_upd->execute([$codigo['total_monto'], $presupuesto['id']]);
            
            echo "  Col4 AHORA: " . number_format($codigo['total_monto'], 2) . "\n";
            $corregidas_presupuesto++;
            echo "\n";
        }
    }
    
    echo "✅ Se corrigieron $corregidas_presupuesto presupuestos\n\n";
    
    // PASO 3: Verificar que todo esté correcto
    echo "3️⃣  VERIFICACIÓN FINAL\n";
    echo str_repeat("-", 80) . "\n";
    
    // Verificar items
    $stmt = $db->query("
        SELECT 
            d.id, d.monto, d.cantidad_liquidacion, d.cantidad_pendiente,
            (d.monto - d.cantidad_liquidacion) as esperado
        FROM detalle_certificados d
    ");
    
    $items_check = $stmt->fetchAll();
    $todo_ok = true;
    
    echo "Items corregidos:\n";
    foreach ($items_check as $item) {
        $ok = ($item['cantidad_pendiente'] == $item['esperado']) ? "✅" : "❌";
        echo "$ok ID {$item['id']}: Pendiente {$item['cantidad_pendiente']} = Monto {$item['monto']} - Liquidado {$item['cantidad_liquidacion']}\n";
        
        if ($item['cantidad_pendiente'] != $item['esperado']) {
            $todo_ok = false;
        }
    }
    
    echo "\n";
    
    // Verificar presupuesto
    $stmt = $db->query("
        SELECT p.id, p.codigo_completo, p.col4, COALESCE(SUM(d.monto), 0) as suma_montos
        FROM presupuesto_items p
        LEFT JOIN detalle_certificados d ON d.codigo_completo = p.codigo_completo
        WHERE p.codigo_completo IN (
            SELECT DISTINCT codigo_completo 
            FROM detalle_certificados 
            WHERE codigo_completo IS NOT NULL
        )
        GROUP BY p.id, p.codigo_completo, p.col4
    ");
    
    $presupuesto_check = $stmt->fetchAll();
    
    echo "Presupuestos corregidos:\n";
    foreach ($presupuesto_check as $p) {
        $ok = ($p['col4'] == $p['suma_montos']) ? "✅" : "❌";
        echo "$ok {$p['codigo_completo']}: col4 = " . number_format($p['col4'], 2) . " (suma de montos: " . number_format($p['suma_montos'], 2) . ")\n";
        
        if ($p['col4'] != $p['suma_montos']) {
            $todo_ok = false;
        }
    }
    
    echo "\n";
    
    if ($todo_ok) {
        echo "✅ TODAS LAS CORRECCIONES EXITOSAS!\n";
    } else {
        echo "❌ AÚN HAY PROBLEMAS\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
