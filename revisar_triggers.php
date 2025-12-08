<?php
/**
 * REVISAR TRIGGERS DE CANTIDAD_PENDIENTE Y ORDEN DE EJECUCIÓN
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "REVISAR TRIGGERS Y ORDEN DE EJECUCIÓN\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Ver triggers en orden
    echo "1️⃣  TRIGGERS EN detalle_certificados\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT 
            trigger_name,
            action_timing,
            event_manipulation,
            action_statement
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        ORDER BY event_manipulation, action_timing
    ");
    
    $triggers = $stmt->fetchAll();
    foreach ($triggers as $trg) {
        echo "\n  TRIGGER: {$trg['trigger_name']}\n";
        echo "  Timing: {$trg['action_timing']} {$trg['event_manipulation']}\n";
        echo "  SQL: {$trg['action_statement']}\n";
    }
    
    echo "\n\n";
    
    // 2. Problema: BEFORE INSERT trigger no tiene nombre específico
    // Necesito ver la función trigger_detalle_cantidad_pendiente
    
    echo "2️⃣  VER CÓDIGO DE trigger_detalle_cantidad_pendiente\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT pg_get_functiondef(oid)
        FROM pg_proc
        WHERE proname = 'trigger_detalle_cantidad_pendiente'
    ");
    
    $result = $stmt->fetch();
    if ($result) {
        echo $result[0] . "\n\n";
    } else {
        echo "❌ No existe función trigger_detalle_cantidad_pendiente\n\n";
    }
    
    // 3. Ver código de fn_restar_pendiente_col4
    echo "3️⃣  VER CÓDIGO DE fn_restar_pendiente_col4\n";
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
    
    // 4. Probar INSERT con logging detallado
    echo "4️⃣  PROBAR INSERT CON DETALLE DE VALORES\n";
    echo str_repeat("-", 100) . "\n";
    
    // Crear presupuesto de prueba
    $codigo_test2 = 'TEST-INSERT-' . date('YmdHis');
    $stmt = $db->prepare("
        INSERT INTO presupuesto_items 
        (codigo_completo, col4, col1, col2, col3, fecha_creacion)
        VALUES (?, ?, 1, 1, 1, NOW())
        RETURNING id, col4
    ");
    $stmt->execute([$codigo_test2, 20000]);
    $presupuesto = $stmt->fetch();
    
    echo "Presupuesto: $codigo_test2\n";
    echo "Col4 ANTES: {$presupuesto['col4']}\n\n";
    
    // Crear certificado
    $stmt = $db->prepare("
        INSERT INTO certificados
        (numero_certificado, institucion, seccion_memorando, descripcion, fecha_elaboracion, monto_total, usuario_creacion, fecha_creacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        RETURNING id
    ");
    $stmt->execute(['TEST-' . uniqid(), 'TEST', 'TEST', 'Test', date('Y-m-d'), 2000, 'admin']);
    $cert_id = $stmt->fetchColumn();
    
    // INSERT con valores conocidos
    echo "Insertando:\n";
    echo "  Monto: 2000\n";
    echo "  Cantidad Liquidación: 0\n";
    echo "  Cantidad Pendiente: (debe calcularse a 2000)\n\n";
    
    // Insertar con cantidad_pendiente = NULL (será calculado)
    $stmt = $db->prepare("
        INSERT INTO detalle_certificados
        (certificado_id, monto, cantidad_liquidacion, cantidad_pendiente, codigo_completo)
        VALUES (?, ?, ?, NULL, ?)
        RETURNING id, monto, cantidad_liquidacion, cantidad_pendiente, codigo_completo
    ");
    $stmt->execute([$cert_id, 2000, 0, $codigo_test2]);
    $item = $stmt->fetch();
    
    echo "Item insertado:\n";
    echo "  ID: {$item['id']}\n";
    echo "  Monto: {$item['monto']}\n";
    echo "  Cantidad Liquidación: {$item['cantidad_liquidacion']}\n";
    echo "  Cantidad Pendiente: {$item['cantidad_pendiente']}\n\n";
    
    // Ver col4
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo_test2]);
    $col4_nuevo = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: $col4_nuevo\n";
    echo "Diferencia: " . ($presupuesto['col4'] - $col4_nuevo) . "\n";
    echo "Esperado: 2000.00\n\n";
    
    if ($col4_nuevo == $presupuesto['col4'] - 2000) {
        echo "✅ TRIGGER FUNCIONÓ CORRECTAMENTE\n";
    } else {
        echo "❌ TRIGGER NO FUNCIONÓ\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
