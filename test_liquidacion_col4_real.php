<?php
/**
 * TEST: Verificar que al liquidar se actualiza col4 correctamente
 * CON UN CÓDIGO EXISTENTE EN PRESUPUESTO
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "TEST: Liquidación - Actualización de col4 (CON PRESUPUESTO EXISTENTE)\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $cert = new Certificate();
    
    // USAR UN CÓDIGO EXISTENTE EN PRESUPUESTO
    $codigo_test = '82 00 000 002 003 0200 510203';
    
    // Verificar que existe en presupuesto
    $stmtCheck = $db->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmtCheck->execute([$codigo_test]);
    $presResult = $stmtCheck->fetch();
    
    if (!$presResult) {
        echo "❌ Código no existe en presupuesto\n";
        exit(1);
    }
    
    $col4_antes_total = (float)$presResult['col4'];
    echo "Usando código: $codigo_test\n";
    echo "col4 inicial en presupuesto: " . number_format($col4_antes_total, 2) . "\n\n";
    
    // 1. CREAR CERTIFICADO
    echo "1️⃣  CREANDO CERTIFICADO...\n";
    echo str_repeat("-", 80) . "\n";
    
    $certData = [
        'numero_certificado' => 'TEST-COL4-' . date('YmdHis'),
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
        'programa_codigo' => '82',
        'subprograma_codigo' => '00',
        'proyecto_codigo' => '000',
        'actividad_codigo' => '002',
        'item_codigo' => '510203',
        'ubicacion_codigo' => '0200',
        'fuente_codigo' => '003',
        'organismo_codigo' => '0000',
        'naturaleza_codigo' => '0000',
        'descripcion_item' => 'Item para test de col4',
        'monto' => 1000.00,
        'codigo_completo' => $codigo_test
    ];
    
    $item_id = $cert->createDetail($itemData);
    echo "✓ Item creado: ID $item_id\n";
    
    // Verificar col4 después del INSERT
    $stmtCheck->execute([$codigo_test]);
    $presResult = $stmtCheck->fetch();
    $col4_despues_insert = (float)$presResult['col4'];
    
    echo "\nEstado de col4 después de INSERT:\n";
    echo "  col4 antes: " . number_format($col4_antes_total, 2) . "\n";
    echo "  col4 después: " . number_format($col4_despues_insert, 2) . "\n";
    echo "  Diferencia: " . number_format($col4_despues_insert - $col4_antes_total, 2) . " (debería ser +1000)\n";
    
    if (abs(($col4_despues_insert - $col4_antes_total) - 1000) < 0.01) {
        echo "  ✅ CORRECTO: Trigger INSERT agregó 1000 a col4\n";
    } else {
        echo "  ⚠️  col4 no aumentó en 1000\n";
    }
    
    // 3. LIQUIDAR 700
    echo "\n3️⃣  LIQUIDANDO 700...\n";
    echo str_repeat("-", 80) . "\n";
    
    $resultado = $cert->updateLiquidacion($item_id, 700);
    
    // Verificar col4 después de liquidación
    $stmtCheck->execute([$codigo_test]);
    $presResult = $stmtCheck->fetch();
    $col4_despues_liquidacion = (float)$presResult['col4'];
    
    echo "Resultado de liquidación:\n";
    echo "  cantidad_liquidada: " . number_format($resultado['cantidad_liquidada'], 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($resultado['cantidad_pendiente'], 2) . "\n";
    
    echo "\nEstado de col4 después de liquidación:\n";
    echo "  col4 antes de liquidación: " . number_format($col4_despues_insert, 2) . "\n";
    echo "  col4 después de liquidación: " . number_format($col4_despues_liquidacion, 2) . "\n";
    echo "  Diferencia: " . number_format($col4_despues_insert - $col4_despues_liquidacion, 2) . " (debería ser -300)\n";
    
    $esperado_col4 = $col4_despues_insert - 300;
    if (abs($col4_despues_liquidacion - $esperado_col4) < 0.01) {
        echo "  ✅ CORRECTO: col4 se restó en 300 (cantidad_pendiente)\n";
    } else {
        echo "  ❌ ERROR: col4 no se restó correctamente\n";
    }
    
    // 4. LIQUIDAR MÁS
    echo "\n4️⃣  LIQUIDANDO OTROS 200 (total 900)...\n";
    echo str_repeat("-", 80) . "\n";
    
    $resultado2 = $cert->updateLiquidacion($item_id, 900);
    
    // Verificar col4 final
    $stmtCheck->execute([$codigo_test]);
    $presResult = $stmtCheck->fetch();
    $col4_final = (float)$presResult['col4'];
    
    echo "Resultado de liquidación:\n";
    echo "  cantidad_pendiente: " . number_format($resultado2['cantidad_pendiente'], 2) . "\n";
    
    echo "\nEstado de col4 después de segunda liquidación:\n";
    echo "  col4 anterior: " . number_format($col4_despues_liquidacion, 2) . "\n";
    echo "  col4 actual: " . number_format($col4_final, 2) . "\n";
    echo "  Diferencia: " . number_format($col4_despues_liquidacion - $col4_final, 2) . " (debería ser -200)\n";
    
    $esperado_col4_final = $col4_antes_total + 1000 - 100; // monto inicial + item - pendiente final
    if (abs($col4_final - $esperado_col4_final) < 0.01) {
        echo "  ✅ CORRECTO: col4 se restó correctamente en cada liquidación\n";
    } else {
        echo "  ⚠️  col4 = " . number_format($col4_final, 2) . ", esperado = " . number_format($esperado_col4_final, 2) . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ TEST COMPLETADO\n";
    echo "📊 Resumen:\n";
    echo "   col4 inicial: " . number_format($col4_antes_total, 2) . "\n";
    echo "   col4 final: " . number_format($col4_final, 2) . "\n";
    echo "   Cambio neto: " . number_format($col4_final - $col4_antes_total, 2) . "\n";
    echo str_repeat("=", 80) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
?>
