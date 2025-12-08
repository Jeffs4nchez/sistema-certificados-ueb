<?php
/**
 * DIAGNÓSTICO: ¿Por qué col4 no se actualiza en presupuesto?
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "DIAGNÓSTICO: col4 en presupuesto_items\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. VER CERTIFICADOS ACTUALES
    echo "1️⃣  CERTIFICADOS CREADOS\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT id, numero_certificado, monto_total, total_liquidado, total_pendiente
        FROM certificados
        ORDER BY id DESC
        LIMIT 5
    ");
    
    $certificados = $stmt->fetchAll();
    echo "Total certificados: " . count($certificados) . "\n\n";
    
    foreach ($certificados as $cert) {
        echo "ID {$cert['id']}: {$cert['numero_certificado']}\n";
        echo "  Monto total: " . number_format($cert['monto_total'], 2) . "\n";
        echo "  Liquidado: " . number_format($cert['total_liquidado'], 2) . "\n";
        echo "  Pendiente: " . number_format($cert['total_pendiente'], 2) . "\n";
        echo "\n";
    }
    
    // 2. VER ITEMS DE CERTIFICADO
    echo "2️⃣  ITEMS DE CERTIFICADOS\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT 
            d.id, d.certificado_id, d.monto, 
            d.cantidad_liquidacion, d.cantidad_pendiente, d.codigo_completo
        FROM detalle_certificados d
        ORDER BY d.certificado_id DESC, d.id DESC
        LIMIT 20
    ");
    
    $items = $stmt->fetchAll();
    echo "Total items: " . count($items) . "\n\n";
    
    $suma_monto = 0;
    $suma_liq = 0;
    $suma_pend = 0;
    
    foreach ($items as $item) {
        echo "Item ID {$item['id']} (Cert {$item['certificado_id']})\n";
        echo "  Código: {$item['codigo_completo']}\n";
        echo "  Monto: " . number_format($item['monto'], 2) . "\n";
        echo "  Liquidado: " . number_format($item['cantidad_liquidacion'], 2) . "\n";
        echo "  Pendiente: " . number_format($item['cantidad_pendiente'], 2) . "\n";
        
        $suma_monto += $item['monto'];
        $suma_liq += $item['cantidad_liquidacion'];
        $suma_pend += $item['cantidad_pendiente'];
        
        echo "\n";
    }
    
    echo "SUMAS TOTALES DE ITEMS:\n";
    echo "  Monto: " . number_format($suma_monto, 2) . "\n";
    echo "  Liquidado: " . number_format($suma_liq, 2) . "\n";
    echo "  Pendiente: " . number_format($suma_pend, 2) . "\n\n";
    
    // 3. VERIFICAR PRESUPUESTO_ITEMS
    echo "3️⃣  PRESUPUESTO_ITEMS - COL4 ACTUAL\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("
        SELECT id, codigo_completo, col4 
        FROM presupuesto_items
        WHERE codigo_completo IN (
            SELECT DISTINCT codigo_completo 
            FROM detalle_certificados 
            WHERE codigo_completo IS NOT NULL
        )
    ");
    $stmt->execute();
    
    $presupuestos = $stmt->fetchAll();
    
    if (empty($presupuestos)) {
        echo "❌ NO HAY CÓDIGOS DE PRESUPUESTO QUE COINCIDAN\n\n";
    } else {
        echo "Presupuestos encontrados:\n";
        foreach ($presupuestos as $p) {
            echo "ID {$p['id']}: {$p['codigo_completo']}\n";
            echo "  col4: " . number_format($p['col4'], 2) . "\n";
            echo "\n";
        }
    }
    
    // 4. VERIFICAR TRIGGERS
    echo "4️⃣  VERIFICAR TRIGGERS\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT trigger_name, event_manipulation, action_timing
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        ORDER BY trigger_name
    ");
    
    $triggers = $stmt->fetchAll();
    
    echo "Triggers en detalle_certificados:\n";
    foreach ($triggers as $trg) {
        echo "  ✓ {$trg['trigger_name']} ({$trg['action_timing']} {$trg['event_manipulation']})\n";
    }
    
    if (empty($triggers)) {
        echo "  ❌ NO HAY TRIGGERS!\n";
    }
    
    echo "\n";
    
    // 5. RECOMENDACIONES
    echo "5️⃣  RECOMENDACIONES\n";
    echo str_repeat("-", 80) . "\n";
    
    if (empty($presupuestos)) {
        echo "❌ PROBLEMA: Los códigos en detalle_certificados NO coinciden con presupuesto_items\n";
        echo "   Solución: Verificar que el código_completo esté EXACTAMENTE igual\n\n";
    }
    
    if (empty($triggers)) {
        echo "❌ PROBLEMA: No hay triggers en detalle_certificados\n";
        echo "   Solución: Ejecutar create_totales_triggers.php\n\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
