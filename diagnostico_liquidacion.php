<?php
/**
 * DIAGNÓSTICO - QUÉ SE ACTUALIZA CUANDO LIQUIDAS
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "DIAGNÓSTICO - LIQUIDACIÓN\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $cert = new Certificate();
    $db = Database::getInstance()->getConnection();
    
    // Obtener un certificado con item
    $stmt = $db->query("
        SELECT c.id, c.numero_certificado
        FROM certificados c
        JOIN detalle_certificados d ON c.id = d.certificado_id
        LIMIT 1
    ");
    
    $certificado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$certificado) {
        echo "❌ No hay certificados con items\n";
        exit(1);
    }
    
    $cert_id = $certificado['id'];
    
    // Obtener item
    $stmt = $db->prepare("
        SELECT id, codigo_completo, monto FROM detalle_certificados 
        WHERE certificado_id = ? LIMIT 1
    ");
    $stmt->execute([$cert_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        echo "❌ No hay items\n";
        exit(1);
    }
    
    $item_id = $item['id'];
    $codigo = $item['codigo_completo'];
    $monto = $item['monto'];
    
    echo "Certificado: {$certificado['numero_certificado']}\n";
    echo "Item ID: $item_id\n";
    echo "Código: $codigo\n";
    echo "Monto: " . number_format($monto, 2) . "\n\n";
    
    // ANTES
    echo "ESTADO ANTES:\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("
        SELECT 
            cantidad_liquidacion as det_liq,
            cantidad_pendiente as det_pend
        FROM detalle_certificados WHERE id = ?
    ");
    $stmt->execute([$item_id]);
    $antes_det = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("
        SELECT col4, col7, col8 FROM presupuesto_items WHERE codigo_completo = ?
    ");
    $stmt->execute([$codigo]);
    $antes_pres = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Detalle:\n";
    echo "  cantidad_liquidacion: " . number_format($antes_det['det_liq'], 2) . "\n";
    echo "  cantidad_pendiente: " . number_format($antes_det['det_pend'], 2) . "\n";
    echo "Presupuesto:\n";
    echo "  col4 (Total Certificado): " . number_format($antes_pres['col4'], 2) . "\n";
    echo "  col7 (Total Liquidado): " . number_format($antes_pres['col7'], 2) . "\n";
    echo "  col8 (Saldo): " . number_format($antes_pres['col8'], 2) . "\n\n";
    
    // LIQUIDAR
    echo "LIQUIDANDO $500...\n";
    echo str_repeat("-", 80) . "\n";
    
    $resultado = $cert->updateLiquidacion($item_id, 500);
    
    echo "Resultado:\n";
    echo "  cantidad_liquidada: " . number_format($resultado['cantidad_liquidada'], 2) . "\n";
    echo "  total_liquidado: " . number_format($resultado['total_liquidado'], 2) . "\n\n";
    
    // DESPUÉS
    echo "ESTADO DESPUÉS:\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("
        SELECT 
            cantidad_liquidacion as det_liq,
            cantidad_pendiente as det_pend
        FROM detalle_certificados WHERE id = ?
    ");
    $stmt->execute([$item_id]);
    $despues_det = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("
        SELECT col4, col7, col8 FROM presupuesto_items WHERE codigo_completo = ?
    ");
    $stmt->execute([$codigo]);
    $despues_pres = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Detalle:\n";
    echo "  cantidad_liquidacion: " . number_format($despues_det['det_liq'], 2) . " (cambió: " . ($despues_det['det_liq'] != $antes_det['det_liq'] ? "✅ SÍ" : "❌ NO") . ")\n";
    echo "  cantidad_pendiente: " . number_format($despues_det['det_pend'], 2) . " (cambió: " . ($despues_det['det_pend'] != $antes_det['det_pend'] ? "✅ SÍ" : "❌ NO") . ")\n";
    echo "Presupuesto:\n";
    echo "  col4: " . number_format($despues_pres['col4'], 2) . " (cambió: " . ($despues_pres['col4'] != $antes_pres['col4'] ? "⚠️ SÍ" : "✅ NO") . ")\n";
    echo "  col7: " . number_format($despues_pres['col7'], 2) . " (cambió: " . ($despues_pres['col7'] != $antes_pres['col7'] ? "⚠️ SÍ" : "✅ NO") . ")\n";
    echo "  col8: " . number_format($despues_pres['col8'], 2) . " (cambió: " . ($despues_pres['col8'] != $antes_pres['col8'] ? "⚠️ SÍ" : "✅ NO") . ")\n\n";
    
    // CONCLUSIONES
    echo "ANÁLISIS:\n";
    echo str_repeat("-", 80) . "\n";
    
    if ($despues_det['det_liq'] != $antes_det['det_liq']) {
        echo "✅ cantidad_liquidacion SE ACTUALIZA correctamente\n";
    } else {
        echo "❌ cantidad_liquidacion NO se actualiza\n";
    }
    
    if ($despues_det['det_pend'] != $antes_det['det_pend']) {
        echo "✅ cantidad_pendiente SE ACTUALIZA correctamente\n";
    } else {
        echo "⚠️  cantidad_pendiente NO se actualiza\n";
    }
    
    if ($despues_pres['col7'] == $antes_pres['col7']) {
        echo "✅ col7 NO CAMBIA (correcto)\n";
    } else {
        echo "⚠️  col7 CAMBIÓ (problema - hay código que la modifica)\n";
    }
    
    if ($despues_pres['col8'] == $antes_pres['col8']) {
        echo "✅ col8 NO CAMBIA (correcto)\n";
    } else {
        echo "⚠️  col8 CAMBIÓ (problema - hay código que la modifica)\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
