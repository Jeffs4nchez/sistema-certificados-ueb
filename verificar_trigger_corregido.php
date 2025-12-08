<?php
/**
 * Script para verificar el comportamiento del trigger actualizado
 */

require_once __DIR__ . '/app/Database.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ VERIFICACIÓN DEL TRIGGER ACTUALIZADO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$db = Database::getInstance()->getConnection();

// Buscar un item de prueba
$query = "
SELECT 
    dc.id,
    dc.codigo_completo,
    dc.monto,
    dc.cantidad_liquidacion,
    pi.col4,
    pi.saldo_disponible,
    (dc.monto - dc.cantidad_liquidacion) as saldo_pendiente_item
FROM detalle_certificados dc
LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
LIMIT 1
";

try {
    $stmt = $db->query($query);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        $id = $item['id'];
        $codigo = $item['codigo_completo'];
        $monto = floatval($item['monto']);
        $liquidacion_actual = floatval($item['cantidad_liquidacion']);
        $col4_antes = floatval($item['col4']);
        $saldo_antes = floatval($item['saldo_disponible']);
        $saldo_pendiente = floatval($item['saldo_pendiente_item']);
        
        echo "📋 ITEM DE PRUEBA:\n";
        echo "───────────────────────────────────────────────────────────────\n";
        echo "ID: $id\n";
        echo "Código: $codigo\n";
        echo "Monto: \$$monto\n";
        echo "Liquidación actual: \$$liquidacion_actual\n";
        echo "Saldo pendiente item: \$$saldo_pendiente\n";
        echo "Col4 antes: \$$col4_antes\n";
        echo "Saldo disponible antes: \$$saldo_antes\n\n";
        
        // Test: aumentar liquidación
        echo "🧪 TEST: AUMENTAR LIQUIDACIÓN\n";
        echo "───────────────────────────────────────────────────────────────\n";
        
        $liquidacion_nueva = $liquidacion_actual + 100;
        $diferencia = $liquidacion_nueva - $liquidacion_actual;
        
        echo "Cambio: \$$liquidacion_actual → \$$liquidacion_nueva\n";
        echo "Diferencia: +\$$diferencia\n";
        echo "Col4 debe disminuir en: \$$diferencia\n\n";
        
        // Ejecutar UPDATE
        echo "📝 Ejecutando UPDATE...\n";
        $update_query = "UPDATE detalle_certificados SET cantidad_liquidacion = ? WHERE id = ?";
        $stmt_update = $db->prepare($update_query);
        $stmt_update->execute([$liquidacion_nueva, $id]);
        
        // Verificar resultado
        sleep(1); // Dar tiempo al trigger
        
        $query_verify = "
        SELECT 
            dc.cantidad_liquidacion,
            pi.col4,
            pi.saldo_disponible,
            (dc.monto - dc.cantidad_liquidacion) as saldo_pendiente_nuevo
        FROM detalle_certificados dc
        LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
        WHERE dc.id = ?
        ";
        
        $stmt_verify = $db->prepare($query_verify);
        $stmt_verify->execute([$id]);
        $item_updated = $stmt_verify->fetch(PDO::FETCH_ASSOC);
        
        echo "\n✅ RESULTADOS DESPUÉS DEL TRIGGER:\n";
        echo "───────────────────────────────────────────────────────────────\n";
        
        $col4_despues = floatval($item_updated['col4']);
        $saldo_despues = floatval($item_updated['saldo_disponible']);
        $saldo_pendiente_nuevo = floatval($item_updated['saldo_pendiente_nuevo']);
        
        echo "Liquidación: \$$liquidacion_actual → \$$liquidacion_nueva ✅\n";
        echo "Col4: \$$col4_antes → \$$col4_despues\n";
        
        if ($col4_despues === ($col4_antes - $diferencia)) {
            echo "   ✅ CORRECTO: Se restó \$$diferencia\n";
        } else {
            echo "   ❌ INCORRECTO: Se esperaba \$" . ($col4_antes - $diferencia) . "\n";
        }
        
        echo "Saldo disponible: \$$saldo_antes → \$$saldo_despues\n";
        echo "Saldo pendiente del item: \$$saldo_pendiente → \$$saldo_pendiente_nuevo\n\n";
        
        // Revertir cambio
        echo "🔄 Revertiendo cambio...\n";
        $revert_query = "UPDATE detalle_certificados SET cantidad_liquidacion = ? WHERE id = ?";
        $stmt_revert = $db->prepare($revert_query);
        $stmt_revert->execute([$liquidacion_actual, $id]);
        
        echo "✅ Revertido\n\n";
        
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "✅ VERIFICACIÓN COMPLETADA\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        echo "El trigger está configurado para:\n";
        echo "• Restar de col4 la diferencia de liquidación\n";
        echo "• Fórmula: col4 = col4 - (cantidad_liquidacion_nueva - cantidad_liquidacion_anterior)\n";
        echo "• Cada item de detalle_certificados afecta a su propio código_completo\n";
        
    } else {
        echo "⚠️  No hay items para verificar\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>
