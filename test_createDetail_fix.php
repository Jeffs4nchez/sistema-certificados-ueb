<?php
/**
 * TEST: Verificar que createDetail() calcula cantidad_pendiente correctamente
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "TEST: createDetail() - Inicialización de cantidad_pendiente\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $cert = new Certificate();
    
    // 1. OBTENER O CREAR UN CERTIFICADO DE PRUEBA
    echo "1️⃣  PREPARANDO DATOS DE PRUEBA...\n";
    echo str_repeat("-", 80) . "\n";
    
    // Crear certificado de prueba
    $certData = [
        'numero_certificado' => 'TEST-' . date('YmdHis'),
        'institucion' => 'Test Institution',
        'seccion_memorando' => '001',
        'descripcion' => 'Certificado de Prueba para cantidad_pendiente',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 5000,
        'unid_ejecutora' => 'TEST',
        'usuario_creacion' => 'test_user'
    ];
    
    $cert_id = $cert->createCertificate($certData);
    echo "✓ Certificado creado: ID $cert_id\n";
    
    // 2. CREAR ITEM SIN LIQUIDACION
    echo "\n2️⃣  CREANDO ITEM SIN LIQUIDACIÓN...\n";
    echo str_repeat("-", 80) . "\n";
    
    $itemData1 = [
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
        'descripcion_item' => 'Item de Prueba 1 - SIN Liquidación',
        'monto' => 1500.00,
        'codigo_completo' => '01 00 001 002 001 0200 510203'
    ];
    
    $item1_id = $cert->createDetail($itemData1);
    
    $stmtCheck1 = $db->prepare("
        SELECT monto, cantidad_liquidacion, cantidad_pendiente
        FROM detalle_certificados WHERE id = ?
    ");
    $stmtCheck1->execute([$item1_id]);
    $result1 = $stmtCheck1->fetch();
    
    echo "Item 1 (SIN liquidación):\n";
    echo "  monto: " . number_format($result1['monto'], 2) . "\n";
    echo "  cantidad_liquidacion: " . number_format($result1['cantidad_liquidacion'] ?? 0, 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($result1['cantidad_pendiente'], 2) . "\n";
    
    $esperado1 = $result1['monto'] - ($result1['cantidad_liquidacion'] ?? 0);
    if (abs($result1['cantidad_pendiente'] - $esperado1) < 0.01) {
        echo "  ✅ CORRECTO: cantidad_pendiente = monto - liquidacion\n";
    } else {
        echo "  ❌ ERROR: cantidad_pendiente no es la fórmula correcta\n";
    }
    
    // 3. CREAR ITEM CON LIQUIDACION INICIAL
    echo "\n3️⃣  CREANDO ITEM CON LIQUIDACIÓN INICIAL...\n";
    echo str_repeat("-", 80) . "\n";
    
    $itemData2 = [
        'certificado_id' => $cert_id,
        'programa_codigo' => '01',
        'subprograma_codigo' => '00',
        'proyecto_codigo' => '001',
        'actividad_codigo' => '003',
        'item_codigo' => '510204',
        'ubicacion_codigo' => '0200',
        'fuente_codigo' => '001',
        'organismo_codigo' => '0000',
        'naturaleza_codigo' => '0000',
        'descripcion_item' => 'Item de Prueba 2 - CON Liquidación',
        'monto' => 2000.00,
        'cantidad_liquidacion' => 800.00,  // Ya liquidado
        'codigo_completo' => '01 00 001 003 001 0200 510204'
    ];
    
    $item2_id = $cert->createDetail($itemData2);
    
    $stmtCheck2 = $db->prepare("
        SELECT monto, cantidad_liquidacion, cantidad_pendiente
        FROM detalle_certificados WHERE id = ?
    ");
    $stmtCheck2->execute([$item2_id]);
    $result2 = $stmtCheck2->fetch();
    
    echo "Item 2 (CON liquidación inicial $800):\n";
    echo "  monto: " . number_format($result2['monto'], 2) . "\n";
    echo "  cantidad_liquidacion: " . number_format($result2['cantidad_liquidacion'] ?? 0, 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($result2['cantidad_pendiente'], 2) . "\n";
    
    $esperado2 = $result2['monto'] - ($result2['cantidad_liquidacion'] ?? 0);
    if (abs($result2['cantidad_pendiente'] - $esperado2) < 0.01) {
        echo "  ✅ CORRECTO: cantidad_pendiente = monto - liquidacion = " . number_format($esperado2, 2) . "\n";
    } else {
        echo "  ❌ ERROR: cantidad_pendiente no es la fórmula correcta\n";
    }
    
    // 4. VERIFICAR TOTALES EN CERTIFICADO
    echo "\n4️⃣  VERIFICANDO TOTALES EN CERTIFICADO...\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmtCert = $db->prepare("
        SELECT monto_total, total_liquidado, total_pendiente
        FROM certificados WHERE id = ?
    ");
    $stmtCert->execute([$cert_id]);
    $cert_result = $stmtCert->fetch();
    
    echo "Certificado:\n";
    echo "  monto_total: " . number_format($cert_result['monto_total'], 2) . "\n";
    echo "  total_liquidado: " . number_format($cert_result['total_liquidado'] ?? 0, 2) . "\n";
    echo "  total_pendiente: " . number_format($cert_result['total_pendiente'] ?? 0, 2) . "\n";
    
    $suma_montos = $result1['monto'] + $result2['monto'];
    $suma_liquidados = ($result1['cantidad_liquidacion'] ?? 0) + ($result2['cantidad_liquidacion'] ?? 0);
    $suma_pendientes = $result1['cantidad_pendiente'] + $result2['cantidad_pendiente'];
    
    echo "\nVerificación de totales:\n";
    echo "  Suma de montos: " . number_format($suma_montos, 2) . "\n";
    echo "  Suma de liquidados: " . number_format($suma_liquidados, 2) . "\n";
    echo "  Suma de pendientes: " . number_format($suma_pendientes, 2) . "\n";
    
    if (abs($cert_result['total_liquidado'] - $suma_liquidados) < 0.01 &&
        abs($cert_result['total_pendiente'] - $suma_pendientes) < 0.01) {
        echo "  ✅ TOTALES CORRECTOS\n";
    } else {
        echo "  ⚠️  TOTALES NO COINCIDEN (revisar triggers)\n";
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
