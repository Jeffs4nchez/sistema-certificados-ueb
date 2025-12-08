<?php
/**
 * PRUEBA: Verificar que el trigger actualiza cantidad_pendiente correctamente
 * al hacer UPDATE en cantidad_liquidacion
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "PRUEBA: Trigger actualiza cantidad_pendiente en tiempo real\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Obtener un item
    $stmt = $db->query("
        SELECT id, monto, cantidad_liquidacion, cantidad_pendiente
        FROM detalle_certificados
        LIMIT 1
    ");
    
    $item = $stmt->fetch();
    
    if (!$item) {
        echo "❌ No hay items\n";
        exit(1);
    }
    
    echo "1️⃣  ESTADO INICIAL\n";
    echo str_repeat("-", 80) . "\n";
    echo "Item ID: {$item['id']}\n";
    echo "Monto: " . number_format($item['monto'], 2) . "\n";
    echo "Liquidado: " . number_format($item['cantidad_liquidacion'], 2) . "\n";
    echo "Pendiente: " . number_format($item['cantidad_pendiente'], 2) . "\n\n";
    
    // Hacer UPDATE directamente en la BD
    echo "2️⃣  HACIENDO UPDATE: cantidad_liquidacion = 700\n";
    echo str_repeat("-", 80) . "\n";
    
    $new_liquidacion = 700;
    
    $update_stmt = $db->prepare("
        UPDATE detalle_certificados
        SET cantidad_liquidacion = ?
        WHERE id = ?
    ");
    
    $update_stmt->execute([$new_liquidacion, $item['id']]);
    echo "✅ UPDATE ejecutado\n\n";
    
    // Leer de nuevo
    echo "3️⃣  ESTADO DESPUÉS DEL UPDATE\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("
        SELECT id, monto, cantidad_liquidacion, cantidad_pendiente
        FROM detalle_certificados
        WHERE id = ?
    ");
    
    $stmt->execute([$item['id']]);
    $item_nuevo = $stmt->fetch();
    
    echo "Monto: " . number_format($item_nuevo['monto'], 2) . "\n";
    echo "Liquidado: " . number_format($item_nuevo['cantidad_liquidacion'], 2) . "\n";
    echo "Pendiente: " . number_format($item_nuevo['cantidad_pendiente'], 2) . "\n\n";
    
    // Verificar
    echo "4️⃣  VERIFICACIÓN\n";
    echo str_repeat("-", 80) . "\n";
    
    $esperado_pendiente = $item_nuevo['monto'] - $item_nuevo['cantidad_liquidacion'];
    
    echo "Pendiente esperado: " . number_format($esperado_pendiente, 2) . "\n";
    echo "Pendiente actual: " . number_format($item_nuevo['cantidad_pendiente'], 2) . "\n";
    
    if ($item_nuevo['cantidad_pendiente'] == $esperado_pendiente) {
        echo "\n✅✅✅ TRIGGER FUNCIONÓ CORRECTAMENTE ✅✅✅\n";
        echo "Fórmula: cantidad_pendiente = {$item_nuevo['monto']} - {$item_nuevo['cantidad_liquidacion']} = {$item_nuevo['cantidad_pendiente']}\n";
    } else {
        echo "\n❌ TRIGGER NO FUNCIONÓ\n";
        echo "Esperado: " . number_format($esperado_pendiente, 2) . "\n";
        echo "Actual: " . number_format($item_nuevo['cantidad_pendiente'], 2) . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
