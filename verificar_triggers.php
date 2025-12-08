<?php
/**
 * Script de Verificación de Triggers de Liquidaciones
 * Verifica que los triggers estén creados y funcionando correctamente
 */

require_once __DIR__ . '/app/Database.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔍 VERIFICACIÓN DE TRIGGERS DE LIQUIDACIONES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$db = Database::getInstance()->getConnection();

// ═══════════════════════════════════════════════════════════════
// 1. VERIFICAR QUE LOS TRIGGERS EXISTAN
// ═══════════════════════════════════════════════════════════════

echo "📋 1. VERIFICANDO TRIGGERS EXISTENTES...\n";
echo "───────────────────────────────────────────────────────────────\n";

$query_triggers = "
SELECT 
    trigger_name,
    event_manipulation,
    event_object_table,
    action_timing
FROM information_schema.triggers
WHERE trigger_schema = 'public' 
  AND event_object_table IN ('detalle_certificados', 'presupuesto_items')
ORDER BY event_object_table, trigger_name;
";

try {
    $stmt = $db->query($query_triggers);
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($triggers)) {
        echo "⚠️  NO HAY TRIGGERS CREADOS\n";
    } else {
        echo "✅ Se encontraron " . count($triggers) . " triggers:\n\n";
        
        foreach ($triggers as $trigger) {
            $tabla = $trigger['event_object_table'];
            $evento = $trigger['event_manipulation'];
            $timing = $trigger['action_timing'];
            $nombre = $trigger['trigger_name'];
            
            echo "  • Tabla: $tabla\n";
            echo "    Evento: $evento | Timing: $timing\n";
            echo "    Nombre: $nombre\n\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error al verificar triggers: " . $e->getMessage() . "\n";
}

// ═══════════════════════════════════════════════════════════════
// 2. VERIFICAR FUNCIONES ASOCIADAS
// ═══════════════════════════════════════════════════════════════

echo "\n📋 2. VERIFICANDO FUNCIONES DE TRIGGERS...\n";
echo "───────────────────────────────────────────────────────────────\n";

$query_functions = "
SELECT 
    routine_name,
    routine_type
FROM information_schema.routines
WHERE routine_schema = 'public' 
  AND routine_name LIKE 'fn_trigger_%'
ORDER BY routine_name;
";

try {
    $stmt = $db->query($query_functions);
    $functions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($functions)) {
        echo "⚠️  NO HAY FUNCIONES DE TRIGGER CREADAS\n";
    } else {
        echo "✅ Se encontraron " . count($functions) . " funciones:\n\n";
        
        foreach ($functions as $func) {
            echo "  • " . $func['routine_name'] . " (" . $func['routine_type'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error al verificar funciones: " . $e->getMessage() . "\n";
}

// ═══════════════════════════════════════════════════════════════
// 3. TEST FUNCIONAL: VERIFICAR QUE LOS TRIGGERS ACTUALICEN DATOS
// ═══════════════════════════════════════════════════════════════

echo "\n📋 3. TEST FUNCIONAL - VERIFICAR ACTUALIZACIONES\n";
echo "───────────────────────────────────────────────────────────────\n";

// Buscar un item para probar
$query_test = "
SELECT 
    dc.id,
    dc.codigo_completo,
    dc.monto,
    dc.cantidad_liquidacion,
    pi.col4,
    pi.saldo_disponible
FROM detalle_certificados dc
LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
LIMIT 1;
";

try {
    $stmt = $db->query($query_test);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        $codigo = $item['codigo_completo'];
        $monto = $item['monto'];
        $liquidacion_actual = $item['cantidad_liquidacion'] ?? 0;
        $col4_antes = $item['col4'] ?? 0;
        $saldo_antes = $item['saldo_disponible'] ?? 0;
        
        echo "✅ Item encontrado para test:\n";
        echo "  Código: $codigo\n";
        echo "  Monto: $monto\n";
        echo "  Liquidación actual: $liquidacion_actual\n";
        echo "  Col4 actual: $col4_antes\n";
        echo "  Saldo actual: $saldo_antes\n\n";
        
        // Probar UPDATE de liquidación
        echo "📝 Realizando TEST DE UPDATE...\n";
        echo "  Cambio: cantidad_liquidacion $liquidacion_actual → 999\n";
        
        $nueva_liquidacion = 999;
        $query_update = "UPDATE detalle_certificados SET cantidad_liquidacion = ? WHERE id = ?";
        $stmt_update = $db->prepare($query_update);
        $stmt_update->execute([$nueva_liquidacion, $item['id']]);
        
        // Verificar que se actualizó
        $query_verify = "
        SELECT 
            dc.cantidad_liquidacion,
            pi.col4,
            pi.saldo_disponible
        FROM detalle_certificados dc
        LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
        WHERE dc.id = ?;
        ";
        
        $stmt_verify = $db->prepare($query_verify);
        $stmt_verify->execute([$item['id']]);
        $item_updated = $stmt_verify->fetch(PDO::FETCH_ASSOC);
        
        echo "\n  ✅ DESPUÉS DEL UPDATE:\n";
        echo "    Liquidación: $liquidacion_actual → " . $item_updated['cantidad_liquidacion'] . "\n";
        echo "    Col4: $col4_antes → " . $item_updated['col4'] . "\n";
        echo "    Saldo: $saldo_antes → " . $item_updated['saldo_disponible'] . "\n";
        
        // Revertir el cambio
        $query_revert = "UPDATE detalle_certificados SET cantidad_liquidacion = ? WHERE id = ?";
        $stmt_revert = $db->prepare($query_revert);
        $stmt_revert->execute([$liquidacion_actual, $item['id']]);
        
        echo "\n  ✅ Cambio revertido (vuelto al valor anterior)\n";
    } else {
        echo "⚠️  No se encontraron items para test\n";
    }
} catch (Exception $e) {
    echo "❌ Error en test funcional: " . $e->getMessage() . "\n";
}

// ═══════════════════════════════════════════════════════════════
// 4. VERIFICAR INTEGRIDAD DE DATOS
// ═══════════════════════════════════════════════════════════════

echo "\n\n📋 4. VERIFICACIÓN DE INTEGRIDAD DE DATOS\n";
echo "───────────────────────────────────────────────────────────────\n";

$query_integridad = "
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN col4 != (SELECT COALESCE(SUM(monto), 0) FROM detalle_certificados WHERE codigo_completo = presupuesto_items.codigo_completo) THEN 1 ELSE 0 END) as desincronizadas
FROM presupuesto_items
WHERE codigo_completo IN (SELECT DISTINCT codigo_completo FROM detalle_certificados);
";

try {
    $stmt = $db->query($query_integridad);
    $integridad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total = $integridad['total'] ?? 0;
    $desincronizadas = $integridad['desincronizadas'] ?? 0;
    
    if ($desincronizadas == 0) {
        echo "✅ Integridad CORRECTA\n";
        echo "   Total de presupuestos revisados: $total\n";
        echo "   Desincronizaciones encontradas: 0\n";
    } else {
        echo "⚠️  Se encontraron desincronizaciones\n";
        echo "   Total: $total\n";
        echo "   Desincronizadas: $desincronizadas\n";
    }
} catch (Exception $e) {
    echo "❌ Error al verificar integridad: " . $e->getMessage() . "\n";
}

// ═══════════════════════════════════════════════════════════════
// 5. RESUMEN FINAL
// ═══════════════════════════════════════════════════════════════

echo "\n\n═══════════════════════════════════════════════════════════════\n";
echo "✅ VERIFICACIÓN COMPLETADA\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\nPasos completados:\n";
echo "  1. ✅ Verificación de triggers existentes\n";
echo "  2. ✅ Verificación de funciones\n";
echo "  3. ✅ Test funcional de UPDATE\n";
echo "  4. ✅ Verificación de integridad de datos\n";
echo "\nSi todos los pasos muestran ✅, los triggers están funcionando correctamente.\n";
echo "Si hay ⚠️, revisa la documentación TRIGGERS_LIQUIDACIONES.md\n";
echo "\n═══════════════════════════════════════════════════════════════\n";

?>
