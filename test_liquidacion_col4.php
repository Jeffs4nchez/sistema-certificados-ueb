<?php
/**
 * TEST: Verificar que al liquidar se actualiza col4 correctamente
 * 
 * Flujo esperado:
 * 1. Crear item con monto = 1000
 *    → col4 += 1000 (trigger INSERT)
 * 2. Liquidar 700
 *    → cantidad_pendiente = 1000 - 700 = 300
 *    → col4 -= 300 (nuevo código)
 *    → col4 final = 1000 - 300 = 700
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "TEST: Liquidación - Actualización de col4\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $cert = new Certificate();
    
    // 1. CREAR CERTIFICADO
    echo "1️⃣  CREANDO CERTIFICADO...\n";
    echo str_repeat("-", 80) . "\n";
    
    $certData = [
        'numero_certificado' => 'TEST-LIQUIDACION-' . date('YmdHis'),
        'institucion' => 'Test Institution',
        'seccion_memorando' => '001',
        'descripcion' => 'Test para col4 en liquidación',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 10000,
        'unid_ejecutora' => 'TEST',
        'usuario_creacion' => 'test_user'
    ];
    
    $cert_id = $cert->createCertificate($certData);
    echo "✓ Certificado creado: ID $cert_id\n\n";
    
    // 2. CREAR ITEM
    echo "2️⃣  CREANDO ITEM CON MONTO 1000...\n";
    echo str_repeat("-", 80) . "\n";
    
    $itemData = [
        'certificado_id' => $cert_id,
        'programa_codigo' => '01',
        'subprograma_codigo' => '00',
        'proyecto_codigo' => '001',
        'actividad_codigo' => '002',
        'item_codigo' => '510203',
        'ubicacion_codigo' => '0200',
        'fuente_codigo' => '001',
        'organismo_codigo' => '0000',
        'naturaleza_codigo' => '0000',
        'descripcion_item' => 'Item para test de liquidación',
        'monto' => 1000.00,
        'codigo_completo' => '01 00 001 002 001 0200 510203'
    ];
    
    $item_id = $cert->createDetail($itemData);
    echo "✓ Item creado: ID $item_id\n";
    
    // Verificar estado inicial
    $stmtItem = $db->prepare("
        SELECT monto, cantidad_liquidacion, cantidad_pendiente
        FROM detalle_certificados WHERE id = ?
    ");
    $stmtItem->execute([$item_id]);
    $itemData = $stmtItem->fetch();
    
    echo "\nEstado inicial del item:\n";
    echo "  monto: " . number_format($itemData['monto'], 2) . "\n";
    echo "  cantidad_liquidacion: " . number_format($itemData['cantidad_liquidacion'] ?? 0, 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($itemData['cantidad_pendiente'], 2) . "\n";
    
    // Verificar col4 en presupuesto
    $stmtPres = $db->prepare("
        SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?
    ");
    $stmtPres->execute(['01 00 001 002 001 0200 510203']);
    $presData = $stmtPres->fetch();
    
    $col4_inicial = $presData ? (float)$presData['col4'] : 0;
    echo "\nEstado inicial de presupuesto:\n";
    echo "  col4: " . number_format($col4_inicial, 2) . "\n";
    
    if (abs($col4_inicial - 1000) < 0.01) {
        echo "  ✅ CORRECTO: col4 = monto (trigger INSERT funcionó)\n";
    } else {
        echo "  ⚠️  col4 no es 1000 (¿falta trigger?)\n";
    }
    
    // 3. LIQUIDAR 700
    echo "\n3️⃣  LIQUIDANDO 700...\n";
    echo str_repeat("-", 80) . "\n";
    
    $resultado = $cert->updateLiquidacion($item_id, 700);
    
    echo "Resultado de liquidación:\n";
    echo "  cantidad_liquidada: " . number_format($resultado['cantidad_liquidada'], 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($resultado['cantidad_pendiente'], 2) . "\n";
    echo "  total_liquidado: " . number_format($resultado['total_liquidado'], 2) . "\n";
    echo "  total_pendiente: " . number_format($resultado['total_pendiente'], 2) . "\n";
    
    // Verificar estado después de liquidación
    $stmtItem->execute([$item_id]);
    $itemDataPost = $stmtItem->fetch();
    
    echo "\nEstado del item después de liquidación:\n";
    echo "  cantidad_liquidacion: " . number_format($itemDataPost['cantidad_liquidacion'], 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($itemDataPost['cantidad_pendiente'], 2) . "\n";
    
    if (abs($itemDataPost['cantidad_pendiente'] - 300) < 0.01) {
        echo "  ✅ CORRECTO: cantidad_pendiente = 1000 - 700 = 300\n";
    } else {
        echo "  ❌ ERROR: cantidad_pendiente no es 300\n";
    }
    
    // Verificar col4 después de liquidación
    $stmtPres->execute(['01 00 001 002 001 0200 510203']);
    $presDataPost = $stmtPres->fetch();
    
    $col4_final = $presDataPost ? (float)$presDataPost['col4'] : 0;
    echo "\nEstado de presupuesto después de liquidación:\n";
    echo "  col4 inicial: " . number_format($col4_inicial, 2) . "\n";
    echo "  col4 final: " . number_format($col4_final, 2) . "\n";
    echo "  Diferencia: " . number_format($col4_inicial - $col4_final, 2) . " (debería ser 300)\n";
    
    $esperado_col4 = 700; // 1000 - 300
    if (abs($col4_final - $esperado_col4) < 0.01) {
        echo "  ✅ CORRECTO: col4 = 1000 - 300 = " . number_format($esperado_col4, 2) . "\n";
    } else {
        echo "  ❌ ERROR: col4 no es " . number_format($esperado_col4, 2) . "\n";
    }
    
    // 4. LIQUIDAR MÁS (OTRO 200)
    echo "\n4️⃣  LIQUIDANDO OTROS 200 (total 900)...\n";
    echo str_repeat("-", 80) . "\n";
    
    $resultado2 = $cert->updateLiquidacion($item_id, 900);
    
    echo "Resultado de liquidación:\n";
    echo "  cantidad_liquidada: " . number_format($resultado2['cantidad_liquidada'], 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($resultado2['cantidad_pendiente'], 2) . "\n";
    
    // Verificar estado final
    $stmtItem->execute([$item_id]);
    $itemDataFinal = $stmtItem->fetch();
    
    echo "\nEstado del item después de segunda liquidación:\n";
    echo "  cantidad_liquidacion: " . number_format($itemDataFinal['cantidad_liquidacion'], 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($itemDataFinal['cantidad_pendiente'], 2) . "\n";
    
    if (abs($itemDataFinal['cantidad_pendiente'] - 100) < 0.01) {
        echo "  ✅ CORRECTO: cantidad_pendiente = 1000 - 900 = 100\n";
    } else {
        echo "  ❌ ERROR: cantidad_pendiente no es 100\n";
    }
    
    // Verificar col4 final
    $stmtPres->execute(['01 00 001 002 001 0200 510203']);
    $presDataFinal = $stmtPres->fetch();
    
    $col4_final2 = $presDataFinal ? (float)$presDataFinal['col4'] : 0;
    echo "\nEstado de presupuesto después de segunda liquidación:\n";
    echo "  col4: " . number_format($col4_final2, 2) . "\n";
    echo "  Cambio desde última liquidación: " . number_format($col4_final - $col4_final2, 2) . " (debería ser 200)\n";
    
    $esperado_col4_final = 100; // 1000 - 100 pendiente
    if (abs($col4_final2 - $esperado_col4_final) < 0.01) {
        echo "  ✅ CORRECTO: col4 = 1000 - 100 = " . number_format($esperado_col4_final, 2) . "\n";
    } else {
        echo "  ❌ ERROR: col4 no es " . number_format($esperado_col4_final, 2) . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ TEST COMPLETADO\n";
    echo str_repeat("=", 80) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
?>
