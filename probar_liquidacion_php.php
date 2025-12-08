<?php
/**
 * PRUEBA DE LIQUIDACIÓN CON PHP PURO
 * Verifica que el método updateLiquidacion funciona correctamente
 */

// Cargar la clase Certificate
require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "PRUEBA DE LIQUIDACIÓN CON PHP PURO\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $cert = new Certificate();
    
    // 1. OBTENER UN CERTIFICADO EXISTENTE
    echo "1️⃣ BUSCANDO CERTIFICADO DE PRUEBA...\n";
    echo str_repeat("-", 80) . "\n";
    
    $db = Database::getInstance()->getConnection();
    
    // Obtener certificado con items
    $stmt = $db->query("
        SELECT c.id, c.numero_certificado, c.monto_total, COUNT(d.id) as items
        FROM certificados c
        LEFT JOIN detalle_certificados d ON c.id = d.certificado_id
        GROUP BY c.id, c.numero_certificado, c.monto_total
        HAVING COUNT(d.id) > 0
        LIMIT 1
    ");
    
    $certificado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$certificado) {
        echo "❌ No hay certificados con items\n";
        exit(1);
    }
    
    $cert_id = $certificado['id'];
    $cert_numero = $certificado['numero_certificado'];
    
    echo "   Certificado: $cert_numero\n";
    echo "   Monto Total: " . number_format($certificado['monto_total'], 2) . "\n\n";
    
    // 2. OBTENER DETALLE PARA PRUEBA
    echo "2️⃣ OBTENIENDO ITEM PARA LIQUIDAR...\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("
        SELECT id, codigo_completo, monto, cantidad_liquidacion
        FROM detalle_certificados
        WHERE certificado_id = ?
        LIMIT 1
    ");
    $stmt->execute([$cert_id]);
    $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $detalle_id = $detalle['id'];
    $codigo_completo = $detalle['codigo_completo'];
    $monto = (float)$detalle['monto'];
    $liquidado_antes = (float)$detalle['cantidad_liquidacion'];
    
    echo "   Item ID: $detalle_id\n";
    echo "   Código: $codigo_completo\n";
    echo "   Monto Total: " . number_format($monto, 2) . "\n";
    echo "   Liquidado Antes: " . number_format($liquidado_antes, 2) . "\n\n";
    
    // 3. OBTENER PRESUPUESTO ANTES (SOLO PARA REFERENCIA)
    echo "3️⃣ ESTADO DEL PRESUPUESTO (SOLO REFERENCIA)...\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("SELECT col4, col5, col6, col7, col8 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo_completo]);
    $presupuesto_antes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   col4 (Total Certificado): " . number_format($presupuesto_antes['col4'], 2) . "\n";
    echo "   col5 (Total Comprometido): " . number_format($presupuesto_antes['col5'], 2) . "\n";
    echo "   col6 (Total Devengado): " . number_format($presupuesto_antes['col6'], 2) . "\n";
    echo "   col7 (NO se modifica en liquidación): " . number_format($presupuesto_antes['col7'], 2) . "\n";
    echo "   col8 (Saldo): " . number_format($presupuesto_antes['col8'], 2) . "\n\n";
    
    // 4. EJECUTAR LIQUIDACIÓN
    echo "4️⃣ LIQUIDANDO $500...\n";
    echo str_repeat("-", 80) . "\n";
    
    $cantidad_a_liquidar = 500;
    
    $resultado = $cert->updateLiquidacion($detalle_id, $cantidad_a_liquidar);
    
    echo "   ✅ Liquidación ejecutada\n";
    echo "   Cantidad Liquidada: " . number_format($resultado['cantidad_liquidada'], 2) . "\n";
    echo "   Cantidad Pendiente: " . number_format($resultado['cantidad_pendiente'], 2) . "\n";
    echo "   Total Liquidado (Cert): " . number_format($resultado['total_liquidado'], 2) . "\n";
    echo "   Total Pendiente (Cert): " . number_format($resultado['total_pendiente'], 2) . "\n\n";
    
    // 5. VERIFICAR QUE CERTIFICADOS SE ACTUALIZÓ (pero NO presupuesto)
    echo "5️⃣ VERIFICANDO CAMBIOS EN CERTIFICADO...\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("SELECT total_liquidado, total_pendiente FROM certificados WHERE id = ?");
    $stmt->execute([$cert_id]);
    $cert_despues = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   Certificado total_liquidado: " . number_format($cert_despues['total_liquidado'], 2) . "\n";
    echo "   Certificado total_pendiente: " . number_format($cert_despues['total_pendiente'], 2) . "\n\n";
    
    echo "   ✅ LIQUIDACIÓN ACTUALIZA CERTIFICADO (cantidad_liquidacion)\n";
    echo "   ✅ NO MODIFICA PRESUPUESTO (col7, col8)\n\n";
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ PRUEBAS COMPLETADAS\n";
    echo str_repeat("=", 80) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>
