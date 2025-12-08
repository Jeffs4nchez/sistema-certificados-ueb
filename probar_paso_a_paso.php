<?php
/**
 * PROBAR PASO A PASO CON SQL DIRECTO
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "PROBAR PASO A PASO\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Ver función actual
    echo "1️⃣  VER FUNCIÓN ACTUAL\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT pg_get_functiondef(oid)
        FROM pg_proc
        WHERE proname = 'fn_restar_pendiente_col4'
    ");
    
    $result = $stmt->fetch();
    if ($result) {
        echo $result[0] . "\n\n";
    }
    
    // 2. Probar INSERT directo a detalle_certificados
    echo "2️⃣  CREAR PRESUPUESTO DE PRUEBA\n";
    echo str_repeat("-", 100) . "\n";
    
    $codigo_test = 'TEST-' . uniqid();
    
    $stmt = $db->prepare("
        INSERT INTO presupuesto_items 
        (codigo_completo, col4, col1, col2, col3, fecha_creacion)
        VALUES (?, ?, 1, 1, 1, NOW())
        RETURNING id, col4
    ");
    $stmt->execute([$codigo_test, 10000]);
    $presupuesto = $stmt->fetch();
    
    echo "Presupuesto: $codigo_test\n";
    echo "Col4 ANTES: " . number_format($presupuesto['col4'], 2) . "\n";
    echo "ID: {$presupuesto['id']}\n\n";
    
    // 3. INSERT directo a detalle_certificados
    echo "3️⃣  INSERT DIRECTO A DETALLE_CERTIFICADOS\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        INSERT INTO detalle_certificados
        (certificado_id, monto, cantidad_liquidacion, cantidad_pendiente, codigo_completo, descripcion)
        VALUES (?, ?, ?, ?, ?, ?)
        RETURNING id, cantidad_pendiente
    ");
    $stmt->execute([999, 3000, 0, 3000, $codigo_test, 'Test']);
    $item = $stmt->fetch();
    
    echo "Item insertado: ID {$item['id']}\n";
    echo "Cantidad Pendiente: " . number_format($item['cantidad_pendiente'], 2) . "\n";
    echo "El trigger debería haber sido ejecutado aquí...\n\n";
    
    // 4. Ver col4 después
    echo "4️⃣  VERIFICAR COL4 DESPUÉS DE INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo_test]);
    $col4_despues = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: " . number_format($col4_despues, 2) . "\n";
    echo "Diferencia: " . number_format($presupuesto['col4'] - $col4_despues, 2) . "\n";
    echo "Esperado: 3000.00\n\n";
    
    if ($col4_despues == $presupuesto['col4'] - 3000) {
        echo "✅ TRIGGER FUNCIONÓ\n";
    } else {
        echo "❌ TRIGGER NO FUNCIONÓ\n\n";
    }
    
    // 5. Ver logs
    echo "5️⃣  VER LOGS DEL TRIGGER\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT * FROM trigger_log
        WHERE codigo_completo = ?
        ORDER BY fecha_evento DESC
        LIMIT 5
    ");
    $stmt->execute([$codigo_test]);
    
    $logs = $stmt->fetchAll();
    if (count($logs) > 0) {
        foreach ($logs as $log) {
            echo "  [{$log['operacion']}] Pendiente={$log['cantidad_pendiente']} -> {$log['resultado']}\n";
        }
    } else {
        echo "❌ NO HAY LOGS PARA ESTE CÓDIGO\n";
    }
    
    echo "\n";
    
    // 6. Verificar si hay algo raro con los triggers
    echo "6️⃣  LISTAR TODOS LOS TRIGGERS EN detalle_certificados\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT 
            trigger_name, 
            event_manipulation, 
            action_timing,
            action_statement
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        ORDER BY trigger_name
    ");
    
    $triggers = $stmt->fetchAll();
    foreach ($triggers as $trg) {
        echo "  {$trg['trigger_name']}\n";
        echo "    Timing: {$trg['action_timing']} {$trg['event_manipulation']}\n";
        echo "    Action: {$trg['action_statement']}\n";
        echo "\n";
    }
    
    echo str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
