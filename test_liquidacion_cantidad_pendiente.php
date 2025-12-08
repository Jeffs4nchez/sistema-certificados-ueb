<?php
/**
 * PRUEBA: Verificar el flujo completo de liquidación
 * Y ver si cantidad_pendiente se actualiza correctamente
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "PRUEBA: Liquidación y actualización de cantidad_pendiente\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $cert = new Certificate();
    
    // Obtener un item
    $stmt = $db->query("
        SELECT id, monto, cantidad_liquidacion, cantidad_pendiente
        FROM detalle_certificados
        LIMIT 1
    ");
    
    $item = $stmt->fetch();
    
    if (!$item) {
        echo "❌ No hay items en la BD\n";
        exit(1);
    }
    
    echo "1️⃣  ESTADO INICIAL DEL ITEM\n";
    echo str_repeat("-", 80) . "\n";
    echo "ID: {$item['id']}\n";
    echo "Monto: " . number_format($item['monto'], 2) . "\n";
    echo "Liquidado: " . number_format($item['cantidad_liquidacion'], 2) . "\n";
    echo "Pendiente: " . number_format($item['cantidad_pendiente'], 2) . "\n";
    echo "Esperado pendiente: " . number_format($item['monto'] - $item['cantidad_liquidacion'], 2) . "\n\n";
    
    // Verificar si es correcto
    $esperado = $item['monto'] - $item['cantidad_liquidacion'];
    if ($item['cantidad_pendiente'] != $esperado) {
        echo "⚠️  ADVERTENCIA: cantidad_pendiente NO es correcta\n";
        echo "   Tiene: " . number_format($item['cantidad_pendiente'], 2) . "\n";
        echo "   Debería tener: " . number_format($esperado, 2) . "\n\n";
    }
    
    // Hacer una liquidación adicional
    echo "2️⃣  SIMULANDO LIQUIDACIÓN ADICIONAL\n";
    echo str_repeat("-", 80) . "\n";
    
    $liquidacion_adicional = $item['cantidad_liquidacion'] + 50; // Liquidar $50 más
    
    echo "Liquidando $50 más...\n";
    echo "Nueva cantidad_liquidacion: " . number_format($liquidacion_adicional, 2) . "\n";
    echo "Esperado cantidad_pendiente: " . number_format($item['monto'] - $liquidacion_adicional, 2) . "\n\n";
    
    // Llamar a updateLiquidacion
    try {
        $cert->updateLiquidacion($item['id'], $liquidacion_adicional);
        echo "✅ updateLiquidacion ejecutada sin errores\n\n";
    } catch (Exception $e) {
        echo "❌ Error en updateLiquidacion: " . $e->getMessage() . "\n\n";
    }
    
    // VERIFICAR RESULTADO
    echo "3️⃣  VERIFICACIÓN DESPUÉS DE LIQUIDAR\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("
        SELECT id, monto, cantidad_liquidacion, cantidad_pendiente
        FROM detalle_certificados
        WHERE id = ?
    ");
    $stmt->execute([$item['id']]);
    
    $item_actualizado = $stmt->fetch();
    
    echo "Monto: " . number_format($item_actualizado['monto'], 2) . "\n";
    echo "Liquidado: " . number_format($item_actualizado['cantidad_liquidacion'], 2) . "\n";
    echo "Pendiente: " . number_format($item_actualizado['cantidad_pendiente'], 2) . "\n";
    
    $esperado_nuevo = $item_actualizado['monto'] - $item_actualizado['cantidad_liquidacion'];
    echo "Esperado: " . number_format($esperado_nuevo, 2) . "\n\n";
    
    if ($item_actualizado['cantidad_pendiente'] == $esperado_nuevo) {
        echo "✅ cantidad_pendiente se actualizó CORRECTAMENTE\n";
    } else {
        echo "❌ cantidad_pendiente NO se actualizó correctamente\n";
        echo "   Tiene: " . number_format($item_actualizado['cantidad_pendiente'], 2) . "\n";
        echo "   Debería tener: " . number_format($esperado_nuevo, 2) . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
