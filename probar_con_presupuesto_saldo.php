<?php
/**
 * PROBAR CON PRESUPUESTO QUE TENGA SALDO
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "PROBAR CON PRESUPUESTO CON SALDO\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar presupuesto con col4 > 0
    echo "1️⃣  BUSCAR PRESUPUESTO CON SALDO\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT id, codigo_completo, col4
        FROM presupuesto_items
        WHERE col4 > 0
        LIMIT 1
    ");
    
    $presupuesto = $stmt->fetch();
    
    if (!$presupuesto) {
        echo "❌ No hay presupuestos con saldo positivo\n";
        echo "   Creando uno ficticio para prueba...\n\n";
        
        // Crear presupuesto de prueba
        $stmt = $db->prepare("
            INSERT INTO presupuesto_items 
            (codigo_completo, col4, col1, col2, col3, fecha_creacion)
            VALUES (?, ?, 1, 1, 1, NOW())
            RETURNING id, codigo_completo, col4
        ");
        $stmt->execute(['99 99 999 999 999 9999 999999', 5000]);
        $presupuesto = $stmt->fetch();
        echo "✅ Presupuesto de prueba creado\n\n";
    }
    
    $codigo = $presupuesto['codigo_completo'];
    $col4_antes = $presupuesto['col4'];
    
    echo "Presupuesto: $codigo\n";
    echo "Col4 ANTES: " . number_format($col4_antes, 2) . "\n";
    echo "ID Presupuesto: {$presupuesto['id']}\n\n";
    
    // 2. Crear certificado e item
    echo "2️⃣  CREAR CERTIFICADO E ITEM\n";
    echo str_repeat("-", 100) . "\n";
    
    require_once 'app/models/Certificate.php';
    $cert = new Certificate();
    
    $cert_data = [
        'numero_certificado' => 'TEST-SALDO-' . date('YmdHis'),
        'institucion' => 'TEST',
        'seccion_memorando' => 'TEST',
        'descripcion' => 'Test con presupuesto con saldo',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 1500,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $cert->createCertificate($cert_data);
    
    $item_data = [
        'certificado_id' => $cert_id,
        'programa_codigo' => '01',
        'subprograma_codigo' => '00',
        'proyecto_codigo' => '000',
        'actividad_codigo' => '001',
        'item_codigo' => '510001',
        'ubicacion_codigo' => '0200',
        'fuente_codigo' => '001',
        'organismo_codigo' => '0000',
        'naturaleza_codigo' => '0000',
        'descripcion_item' => 'Test item con saldo',
        'monto' => 1500,
        'codigo_completo' => $codigo,
        'cantidad_liquidacion' => 0
    ];
    
    $item_id = $cert->createDetail($item_data);
    echo "✅ Certificado ID $cert_id creado\n";
    echo "✅ Item ID $item_id creado: Monto=1500, Pendiente=1500\n\n";
    
    // 3. Verificar col4
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_despues = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: " . number_format($col4_despues, 2) . "\n";
    echo "Diferencia: " . number_format($col4_antes - $col4_despues, 2) . "\n";
    echo "Esperado: 1500.00 (restó cantidad_pendiente del item)\n\n";
    
    if ($col4_despues == $col4_antes - 1500) {
        echo "✅✅✅ TRIGGER FUNCIONÓ CORRECTAMENTE\n";
    } else {
        echo "❌ ERROR EN TRIGGER\n";
        echo "   Col4 debería ser: " . number_format($col4_antes - 1500, 2) . "\n";
        echo "   Col4 actual es: " . number_format($col4_despues, 2) . "\n\n";
        
        // Ver logs
        echo "3️⃣  REVISAR LOGS DEL TRIGGER\n";
        echo str_repeat("-", 100) . "\n";
        
        $stmt = $db->query("
            SELECT * FROM trigger_log
            WHERE codigo_completo = ? 
            ORDER BY fecha_evento DESC
            LIMIT 3
        ");
        $stmt->execute([$codigo]);
        
        $logs = $stmt->fetchAll();
        foreach ($logs as $log) {
            echo "  [{$log['operacion']}] Pendiente={$log['cantidad_pendiente']} -> {$log['resultado']}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
