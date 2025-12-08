<?php
/**
 * PRUEBA FINAL: Col4 = SUM(cantidad_liquidacion) de cada item por codigo_completo
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "PRUEBA FINAL: Col4 = SUM(cantidad_liquidacion) por codigo_completo\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $cert = new Certificate();
    
    // Obtener código presupuestario
    $stmt = $db->query("SELECT id, codigo_completo FROM presupuesto_items LIMIT 1");
    $presupuesto = $stmt->fetch();
    $codigo = $presupuesto['codigo_completo'];
    
    echo "1️⃣  CREAR CERTIFICADO CON 2 ITEMS DEL MISMO CÓDIGO\n";
    echo str_repeat("-", 100) . "\n";
    
    $cert_data = [
        'numero_certificado' => 'CERT-FINAL-' . date('YmdHis'),
        'institucion' => 'TEST FINAL',
        'seccion_memorando' => 'TEST',
        'descripcion' => 'Prueba final col4',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 2000,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $cert->createCertificate($cert_data);
    echo "✅ Certificado creado: ID $cert_id\n\n";
    
    // Crear 2 items del MISMO código
    echo "2️⃣  CREAR 2 ITEMS CON MISMO CÓDIGO\n";
    echo str_repeat("-", 100) . "\n";
    
    $item_ids = [];
    $montos = [800, 1200];
    
    for ($i = 0; $i < 2; $i++) {
        $item_data = [
            'certificado_id' => $cert_id,
            'programa_codigo' => '01',
            'subprograma_codigo' => '00',
            'proyecto_codigo' => '000',
            'actividad_codigo' => '001',
            'item_codigo' => '510' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            'ubicacion_codigo' => '0200',
            'fuente_codigo' => '001',
            'organismo_codigo' => '0000',
            'naturaleza_codigo' => '0000',
            'descripcion_item' => "Item " . ($i + 1) . " - $" . $montos[$i],
            'monto' => $montos[$i],
            'codigo_completo' => $codigo,
            'cantidad_liquidacion' => 0
        ];
        
        $item_id = $cert->createDetail($item_data);
        $item_ids[] = $item_id;
        echo "✅ Item " . ($i + 1) . ": ID $item_id, Monto $" . number_format($montos[$i], 2) . "\n";
    }
    
    echo "\n3️⃣  ESTADO INICIAL\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_inicial = $stmt->fetchColumn();
    
    echo "Código: $codigo\n";
    echo "Col4 inicial: " . number_format($col4_inicial, 2) . "\n";
    echo "Monto total items: " . number_format(array_sum($montos), 2) . "\n";
    echo "Liquidado total: $0.00\n\n";
    
    // Liquidar primer item
    echo "4️⃣  LIQUIDAR ITEM 1: $600\n";
    echo str_repeat("-", 100) . "\n";
    
    $cert->updateLiquidacion($item_ids[0], 600);
    
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_despues_liq1 = $stmt->fetchColumn();
    
    echo "✅ Item 1 liquidado: $600.00\n";
    echo "Col4 ahora: " . number_format($col4_despues_liq1, 2) . "\n";
    echo "Esperado: $600.00 (suma de liquidaciones)\n";
    echo "Coincide: " . ($col4_despues_liq1 == 600 ? "✅ SÍ" : "❌ NO") . "\n\n";
    
    // Liquidar segundo item
    echo "5️⃣  LIQUIDAR ITEM 2: $800\n";
    echo str_repeat("-", 100) . "\n";
    
    $cert->updateLiquidacion($item_ids[1], 800);
    
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_despues_liq2 = $stmt->fetchColumn();
    
    echo "✅ Item 2 liquidado: $800.00\n";
    echo "Col4 ahora: " . number_format($col4_despues_liq2, 2) . "\n";
    echo "Esperado: $1,400.00 (600 + 800 = suma de liquidaciones)\n";
    echo "Coincide: " . ($col4_despues_liq2 == 1400 ? "✅ SÍ" : "❌ NO") . "\n\n";
    
    // Liquidar más el item 1
    echo "6️⃣  LIQUIDAR MÁS ITEM 1: $100 MÁS (total 700)\n";
    echo str_repeat("-", 100) . "\n";
    
    $cert->updateLiquidacion($item_ids[0], 700);
    
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_despues_liq3 = $stmt->fetchColumn();
    
    echo "✅ Item 1 ahora liquidado: $700.00\n";
    echo "Col4 ahora: " . number_format($col4_despues_liq3, 2) . "\n";
    echo "Esperado: $1,500.00 (700 + 800 = suma de liquidaciones)\n";
    echo "Coincide: " . ($col4_despues_liq3 == 1500 ? "✅ SÍ" : "❌ NO") . "\n\n";
    
    // Validación final
    echo "7️⃣  VALIDACIÓN FINAL\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT SUM(cantidad_liquidacion) as total_liquidado
        FROM detalle_certificados
        WHERE codigo_completo = ?
    ");
    $stmt->execute([$codigo]);
    $suma_liquidaciones = $stmt->fetchColumn();
    
    echo "Sum liquidaciones en BD: " . number_format($suma_liquidaciones, 2) . "\n";
    echo "Col4 en presupuesto: " . number_format($col4_despues_liq3, 2) . "\n";
    
    if ($col4_despues_liq3 == $suma_liquidaciones) {
        echo "\n✅✅✅ SISTEMA FUNCIONANDO CORRECTAMENTE ✅✅✅\n";
        echo "Col4 = SUM(cantidad_liquidacion) por codigo_completo ✅\n";
    } else {
        echo "\n❌ MISMATCH\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
