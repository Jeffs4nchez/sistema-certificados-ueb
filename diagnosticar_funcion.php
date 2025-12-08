<?php
/**
 * DIAGNOSTICAR LA FUNCIÓN: POR QUÉ UPDATE NO FUNCIONA
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "DIAGNOSTICAR FUNCIÓN\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Obtener código de prueba
    $codigo_test = 'TEST-693665d1f3713';
    
    // 1. Ver col4 actual
    echo "1️⃣  COL4 ACTUAL\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT id, codigo_completo, col4
        FROM presupuesto_items
        WHERE codigo_completo = ?
    ");
    $stmt->execute([$codigo_test]);
    
    $presupuesto = $stmt->fetch();
    echo "Presupuesto ID: {$presupuesto['id']}\n";
    echo "Código: {$presupuesto['codigo_completo']}\n";
    echo "Col4 ANTES: {$presupuesto['col4']}\n\n";
    
    // 2. Ejecutar manualmente lo que hace la función
    echo "2️⃣  EJECUTAR MANUALMENTE LA UPDATE\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        UPDATE presupuesto_items
        SET col4 = COALESCE(col4, 0) - ?,
            fecha_actualizacion = NOW()
        WHERE codigo_completo = ?
    ");
    
    $cantidad_pendiente = 3000;
    $stmt->execute([$cantidad_pendiente, $codigo_test]);
    
    $affected = $stmt->rowCount();
    echo "UPDATE ejecutado: {$affected} filas afectadas\n";
    echo "Cantidad a restar: $cantidad_pendiente\n";
    echo "Esperado: Col4 nuevo = {$presupuesto['col4']} - $cantidad_pendiente = " . ($presupuesto['col4'] - $cantidad_pendiente) . "\n\n";
    
    // 3. Ver col4 después
    $stmt = $db->prepare("
        SELECT col4
        FROM presupuesto_items
        WHERE codigo_completo = ?
    ");
    $stmt->execute([$codigo_test]);
    
    $col4_nuevo = $stmt->fetchColumn();
    echo "Col4 DESPUÉS: $col4_nuevo\n";
    echo "Diferencia: " . ($presupuesto['col4'] - $col4_nuevo) . "\n\n";
    
    if ($col4_nuevo == $presupuesto['col4'] - $cantidad_pendiente) {
        echo "✅ UPDATE FUNCIONÓ\n";
    } else {
        echo "❌ UPDATE NO FUNCIONÓ\n";
    }
    
    echo "\n";
    
    // 4. Ver historial de cambios en presupuesto_items
    echo "3️⃣  HISTORIAL DE CAMBIOS EN PRESUPUESTO_ITEMS\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT id, codigo_completo, col4, fecha_actualizacion
        FROM presupuesto_items
        WHERE codigo_completo = ?
        ORDER BY fecha_actualizacion DESC NULLS LAST
    ");
    $stmt->execute([$codigo_test]);
    
    $records = $stmt->fetchAll();
    foreach ($records as $record) {
        $fecha = $record['fecha_actualizacion'] ?? 'No actualizado';
        echo "  ID {$record['id']}: Col4={$record['col4']}, Actualizad o={$fecha}\n";
    }
    
    echo "\n";
    
    // 5. Contar cuántos presupuestos tienen este código
    echo "4️⃣  VERIFICAR SI HAY DUPLICADOS DE CÓDIGO\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT id) as cantidad
        FROM presupuesto_items
        WHERE codigo_completo = ?
    ");
    $stmt->execute([$codigo_test]);
    
    $count = $stmt->fetchColumn();
    echo "Cantidad de presupuestos con este código: $count\n";
    
    if ($count > 1) {
        echo "⚠️  HAY MÚLTIPLES PRESUPUESTOS CON EL MISMO CÓDIGO\n";
        echo "   Eso explica por qué el UPDATE actualiza múltiples registros\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
