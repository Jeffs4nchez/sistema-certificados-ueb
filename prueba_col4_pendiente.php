<?php
/**
 * PRUEBA: Verificar que col4 se resta correctamente por cantidad_pendiente
 * en cada item específico usando codigo_completo
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "PRUEBA: Col4 se resta por cantidad_pendiente de cada item\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $cert = new Certificate();
    
    // PASO 1: Obtener código presupuestario válido
    echo "1️⃣  OBTENER CÓDIGO PRESUPUESTARIO\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("SELECT id, codigo_completo, col4 FROM presupuesto_items LIMIT 1");
    $presupuesto = $stmt->fetch();
    
    $codigo = $presupuesto['codigo_completo'];
    $col4_inicial = $presupuesto['col4'];
    
    echo "Código: $codigo\n";
    echo "Col4 inicial: " . number_format($col4_inicial, 2) . "\n\n";
    
    // PASO 2: Crear certificado e item
    echo "2️⃣  CREAR CERTIFICADO E ITEM\n";
    echo str_repeat("-", 100) . "\n";
    
    $cert_data = [
        'numero_certificado' => 'CERT-COL4-' . date('YmdHis'),
        'institucion' => 'TEST COL4',
        'seccion_memorando' => 'TEST',
        'descripcion' => 'Prueba col4 con cantidad_pendiente',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 800,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $cert->createCertificate($cert_data);
    echo "✅ Certificado creado: ID $cert_id\n";
    
    $item_data = [
        'certificado_id' => $cert_id,
        'programa_codigo' => '01',
        'subprograma_codigo' => '00',
        'proyecto_codigo' => '000',
        'actividad_codigo' => '001',
        'item_codigo' => '510999',
        'ubicacion_codigo' => '0200',
        'fuente_codigo' => '001',
        'organismo_codigo' => '0000',
        'naturaleza_codigo' => '0000',
        'descripcion_item' => 'Item Test Col4',
        'monto' => 800,
        'codigo_completo' => $codigo,
        'cantidad_liquidacion' => 0
    ];
    
    $item_id = $cert->createDetail($item_data);
    echo "✅ Item creado: ID $item_id\n\n";
    
    // PASO 3: Ver estado después de INSERT
    echo "3️⃣  COL4 DESPUÉS DE INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_despues_insert = $stmt->fetchColumn();
    
    echo "Col4 inicial: " . number_format($col4_inicial, 2) . "\n";
    echo "Col4 después INSERT: " . number_format($col4_despues_insert, 2) . "\n";
    
    // Obtener cantidad_pendiente del item
    $stmt = $db->prepare("SELECT monto, cantidad_liquidacion, cantidad_pendiente FROM detalle_certificados WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_info = $stmt->fetch();
    
    echo "Item Monto: " . number_format($item_info['monto'], 2) . "\n";
    echo "Item Liquidado: " . number_format($item_info['cantidad_liquidacion'], 2) . "\n";
    echo "Item Pendiente: " . number_format($item_info['cantidad_pendiente'], 2) . "\n";
    echo "Col4 se restó: " . number_format($col4_inicial - $col4_despues_insert, 2) . " (debería ser " . number_format($item_info['cantidad_pendiente'], 2) . ")\n\n";
    
    // PASO 4: Hacer UPDATE a cantidad_liquidacion
    echo "4️⃣  HACER UPDATE: cantidad_liquidacion = 300\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("UPDATE detalle_certificados SET cantidad_liquidacion = 300 WHERE id = ?");
    $stmt->execute([$item_id]);
    echo "✅ UPDATE ejecutado\n\n";
    
    // PASO 5: Ver estado después de UPDATE
    echo "5️⃣  COL4 DESPUÉS DE UPDATE\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_despues_update = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT monto, cantidad_liquidacion, cantidad_pendiente FROM detalle_certificados WHERE id = ?");
    $stmt->execute([$item_id]);
    $item_info_new = $stmt->fetch();
    
    echo "Col4 después UPDATE: " . number_format($col4_despues_update, 2) . "\n";
    echo "Item Liquidado (nuevo): " . number_format($item_info_new['cantidad_liquidacion'], 2) . "\n";
    echo "Item Pendiente (nuevo): " . number_format($item_info_new['cantidad_pendiente'], 2) . "\n";
    
    // Calcular diferencia
    $diferencia_col4 = $col4_despues_insert - $col4_despues_update;
    $diferencia_esperada = $item_info['cantidad_pendiente'] - $item_info_new['cantidad_pendiente'];
    
    echo "Col4 se restó: " . number_format($diferencia_col4, 2) . "\n";
    echo "Diferencia esperada: " . number_format($diferencia_esperada, 2) . " (800 - 300 = 500 pendiente nuevo, 800 - 0 = 800 pendiente anterior, 800 - 500 = 300 diferencia)\n\n";
    
    // PASO 6: VALIDACIÓN FINAL
    echo "6️⃣  VALIDACIÓN FINAL\n";
    echo str_repeat("-", 100) . "\n";
    
    $esperado_col4_final = $col4_inicial - $item_info_new['cantidad_pendiente'];
    
    echo "Col4 esperado: " . number_format($esperado_col4_final, 2) . " (inicial - pendiente final)\n";
    echo "Col4 actual: " . number_format($col4_despues_update, 2) . "\n";
    
    if ($col4_despues_update == $esperado_col4_final) {
        echo "\n✅✅✅ TRIGGER FUNCIONÓ CORRECTAMENTE ✅✅✅\n";
        echo "Col4 se actualiza correctamente por cantidad_pendiente de cada item\n";
    } else {
        echo "\n❌ COL4 NO COINCIDE\n";
        echo "Esperado: " . number_format($esperado_col4_final, 2) . "\n";
        echo "Actual: " . number_format($col4_despues_update, 2) . "\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
