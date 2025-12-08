<?php
/**
 * Script de Sincronización de Presupuestos y Certificados
 * Regenera los valores de col4, col7 y saldo_disponible basándose en los datos reales
 */

require_once __DIR__ . '/app/Database.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔄 SINCRONIZACIÓN DE PRESUPUESTOS Y LIQUIDACIONES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$db = Database::getInstance()->getConnection();

// ═══════════════════════════════════════════════════════════════
// 1. ENCONTRAR DESINCRONIZACIONES
// ═══════════════════════════════════════════════════════════════

echo "📋 1. BUSCANDO DESINCRONIZACIONES...\n";
echo "───────────────────────────────────────────────────────────────\n";

$query = "
SELECT 
    pi.id,
    pi.codigo_completo,
    pi.col4 as col4_actual,
    COALESCE(SUM(dc.monto), 0)::NUMERIC as col4_esperado,
    COALESCE(SUM(dc.cantidad_liquidacion), 0)::NUMERIC as col7_esperado,
    pi.saldo_disponible as saldo_actual,
    (COALESCE(pi.col1, 0) - COALESCE(SUM(dc.monto), 0))::NUMERIC as saldo_esperado
FROM presupuesto_items pi
LEFT JOIN detalle_certificados dc ON pi.codigo_completo = dc.codigo_completo
WHERE pi.codigo_completo IN (SELECT DISTINCT codigo_completo FROM detalle_certificados)
GROUP BY pi.id, pi.codigo_completo, pi.col4, pi.saldo_disponible, pi.col1
HAVING pi.col4 != COALESCE(SUM(dc.monto), 0)
   OR pi.saldo_disponible != (COALESCE(pi.col1, 0) - COALESCE(SUM(dc.monto), 0))
ORDER BY pi.codigo_completo;
";

try {
    $stmt = $db->query($query);
    $desincronizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($desincronizaciones)) {
        echo "✅ No hay desincronizaciones. Todo está sincronizado.\n";
    } else {
        echo "⚠️  Se encontraron " . count($desincronizaciones) . " desincronizaciones:\n\n";
        
        foreach ($desincronizaciones as $item) {
            echo "Código: " . $item['codigo_completo'] . "\n";
            echo "  Col4: " . $item['col4_actual'] . " → " . $item['col4_esperado'] . "\n";
            echo "  Saldo: " . $item['saldo_actual'] . " → " . $item['saldo_esperado'] . "\n\n";
        }
        
        // ═══════════════════════════════════════════════════════════════
        // 2. SINCRONIZAR DATOS
        // ═══════════════════════════════════════════════════════════════
        
        echo "\n📝 2. SINCRONIZANDO DATOS...\n";
        echo "───────────────────────────────────────────────────────────────\n";
        
        $actualizados = 0;
        $errores = 0;
        
        foreach ($desincronizaciones as $item) {
            try {
                $id = $item['id'];
                $col4 = floatval($item['col4_esperado']);
                $saldo = floatval($item['saldo_esperado']);
                
                $update_query = "UPDATE presupuesto_items SET col4 = ?, saldo_disponible = ?, fecha_actualizacion = NOW() WHERE id = ?";
                $stmt_update = $db->prepare($update_query);
                $stmt_update->execute([$col4, $saldo, $id]);
                
                echo "✅ " . $item['codigo_completo'] . " - Sincronizado\n";
                $actualizados++;
            } catch (Exception $e) {
                echo "❌ " . $item['codigo_completo'] . " - Error: " . $e->getMessage() . "\n";
                $errores++;
            }
        }
        
        // ═══════════════════════════════════════════════════════════════
        // 3. RESUMEN
        // ═══════════════════════════════════════════════════════════════
        
        echo "\n\n═══════════════════════════════════════════════════════════════\n";
        echo "✅ SINCRONIZACIÓN COMPLETADA\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "Actualizados: $actualizados\n";
        echo "Errores: $errores\n";
        echo "═══════════════════════════════════════════════════════════════\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>
