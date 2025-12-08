<?php
/**
 * VER ESTRUCTURA Y PROBAR
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "ESTRUCTURA DE TABLAS Y PRUEBA DIRECTA\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Ver columnas de detalle_certificados
    echo "1️⃣  COLUMNAS DE detalle_certificados\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'detalle_certificados'
        ORDER BY ordinal_position
    ");
    
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        $null = $col['is_nullable'] == 'YES' ? 'NULL' : 'NOT NULL';
        echo "  - {$col['column_name']}: {$col['data_type']} ($null)\n";
    }
    
    echo "\n";
    
    // 2. Crear presupuesto de prueba
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
    
    // 3. Crear certificado válido
    echo "3️⃣  CREAR CERTIFICADO VÁLIDO\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        INSERT INTO certificados
        (numero_certificado, institucion, seccion_memorando, descripcion, fecha_elaboracion, monto_total, usuario_creacion, fecha_creacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        RETURNING id
    ");
    $stmt->execute(['TEST-' . uniqid(), 'TEST', 'TEST', 'Test', date('Y-m-d'), 5000, 'admin']);
    $cert_id = $stmt->fetchColumn();
    echo "Certificado creado: ID $cert_id\n\n";
    
    // 4. INSERT directo a detalle_certificados (CORRECTO)
    echo "4️⃣  INSERT DIRECTO A DETALLE_CERTIFICADOS\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        INSERT INTO detalle_certificados
        (certificado_id, monto, cantidad_liquidacion, cantidad_pendiente, codigo_completo)
        VALUES (?, ?, ?, ?, ?)
        RETURNING id, cantidad_pendiente, codigo_completo
    ");
    $stmt->execute([$cert_id, 3000, 0, 3000, $codigo_test]);
    
    $item = $stmt->fetch();
    
    echo "Item insertado: ID {$item['id']}\n";
    echo "Cantidad Pendiente: " . number_format($item['cantidad_pendiente'], 2) . "\n";
    echo "Código: {$item['codigo_completo']}\n";
    echo "Esperado: El trigger trg_pendiente_insert debería ejecutarse y restar col4...\n\n";
    
    // 5. Ver col4 después
    echo "5️⃣  VERIFICAR COL4 DESPUÉS DE INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("SELECT id, col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo_test]);
    $presupuesto_after = $stmt->fetch();
    
    $col4_despues = $presupuesto_after['col4'];
    
    echo "Col4 DESPUÉS: " . number_format($col4_despues, 2) . "\n";
    echo "Diferencia: " . number_format($presupuesto['col4'] - $col4_despues, 2) . "\n";
    echo "Esperado: 3000.00\n\n";
    
    if ($col4_despues == $presupuesto['col4'] - 3000) {
        echo "✅ TRIGGER FUNCIONÓ\n";
    } else {
        echo "❌ TRIGGER NO FUNCIONÓ\n";
        echo "   Col4 debería ser: " . number_format($presupuesto['col4'] - 3000, 2) . "\n";
        echo "   Col4 actual es: " . number_format($col4_despues, 2) . "\n\n";
    }
    
    // 6. Ver logs
    echo "6️⃣  VER LOGS DEL TRIGGER\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT fecha_evento, operacion, cantidad_pendiente, resultado
        FROM trigger_log
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
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
