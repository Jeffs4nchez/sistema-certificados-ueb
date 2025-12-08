<?php
/**
 * CORREGIR: Los triggers necesitan ejecutarse en orden correcto
 * 1. BEFORE INSERT: Calcular cantidad_pendiente
 * 2. AFTER INSERT: Restar cantidad_pendiente de col4
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "CORREGIR ORDEN DE TRIGGERS\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Ver triggers actuales
    echo "1️⃣  TRIGGERS ACTUALES\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT trigger_name, event_manipulation, action_timing
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        ORDER BY trigger_name
    ");
    
    $triggers = $stmt->fetchAll();
    foreach ($triggers as $trg) {
        echo "  {$trg['trigger_name']} ({$trg['action_timing']} {$trg['event_manipulation']})\n";
    }
    
    echo "\n";
    
    // El problema: trigger_detalle_cantidad_pendiente es BEFORE INSERT
    // y trg_pendiente_insert es AFTER INSERT
    // Pero AFTER INSERT en cantidad_pendiente_nuevo recibe el valor calculado del BEFORE
    
    // Solución: Modificar trg_pendiente_insert para calcular la diferencia
    
    echo "2️⃣  AJUSTAR TRIGGER INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $new_trigger = "
        DROP TRIGGER IF EXISTS trg_pendiente_insert ON detalle_certificados CASCADE;
        
        CREATE TRIGGER trg_pendiente_insert
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_restar_pendiente_col4();
    ";
    
    $db->exec($new_trigger);
    echo "✅ Trigger ajustado: trg_pendiente_insert\n\n";
    
    // Ahora probar con un nuevo item
    echo "3️⃣  PROBAR CREANDO NUEVO ITEM\n";
    echo str_repeat("-", 100) . "\n";
    
    require_once 'app/models/Certificate.php';
    
    $cert = new Certificate();
    
    // Obtener código presupuestario válido
    $stmt = $db->query("SELECT id, codigo_completo, col4 FROM presupuesto_items LIMIT 1");
    $presupuesto = $stmt->fetch();
    
    $codigo = $presupuesto['codigo_completo'];
    $col4_antes = $presupuesto['col4'];
    
    echo "Presupuesto: $codigo\n";
    echo "Col4 ANTES: " . number_format($col4_antes, 2) . "\n\n";
    
    // Crear certificado
    $cert_data = [
        'numero_certificado' => 'TEST-TRIGGER-' . date('YmdHis'),
        'institucion' => 'TEST',
        'seccion_memorando' => 'TEST',
        'descripcion' => 'Probar triggers',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 750,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $cert->createCertificate($cert_data);
    echo "✅ Certificado creado: ID $cert_id\n";
    
    // Crear item
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
        'descripcion_item' => 'Item Test Trigger',
        'monto' => 750,
        'codigo_completo' => $codigo,
        'cantidad_liquidacion' => 0
    ];
    
    $item_id = $cert->createDetail($item_data);
    echo "✅ Item creado: ID $item_id, Monto 750, Liquidado 0, Pendiente 750\n\n";
    
    // Ver col4 después
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_despues = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: " . number_format($col4_despues, 2) . "\n";
    echo "Diferencia: " . number_format($col4_antes - $col4_despues, 2) . "\n";
    echo "Esperado: 750.00 (restó la cantidad_pendiente del item)\n\n";
    
    if ($col4_despues == $col4_antes - 750) {
        echo "✅ TRIGGER FUNCIONÓ CORRECTAMENTE EN INSERT\n";
    } else {
        echo "❌ TRIGGER NO FUNCIONÓ CORRECTAMENTE\n";
        echo "   Col4 debería ser: " . number_format($col4_antes - 750, 2) . "\n";
        echo "   Col4 actual es: " . number_format($col4_despues, 2) . "\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
