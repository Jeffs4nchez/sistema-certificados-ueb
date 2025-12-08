<?php
/**
 * Análisis de items con código 510203
 */

require_once __DIR__ . '/app/Database.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔍 ANÁLISIS DE ITEMS CON CÓDIGO 510203\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$db = Database::getInstance()->getConnection();

// Buscar todos los items 510203
$query = "
SELECT 
    dc.id,
    dc.certificado_id,
    dc.codigo_completo,
    dc.monto,
    dc.cantidad_liquidacion,
    (dc.monto - dc.cantidad_liquidacion) as saldo_pendiente,
    pi.col4,
    pi.saldo_disponible
FROM detalle_certificados dc
LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
WHERE dc.codigo_completo LIKE '%510203%'
ORDER BY dc.id DESC
LIMIT 10
";

try {
    $stmt = $db->query($query);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($items)) {
        echo "No hay items con código 510203\n";
    } else {
        echo "📋 DETALLE_CERTIFICADOS (Liquidaciones):\n";
        echo "───────────────────────────────────────────────────────────────\n";
        
        foreach ($items as $item) {
            echo "\nID: " . $item['id'] . " | Certificado: " . $item['certificado_id'] . "\n";
            echo "  Código: " . $item['codigo_completo'] . "\n";
            echo "  Monto: \$" . number_format($item['monto'], 2) . "\n";
            echo "  Liquidación: \$" . number_format($item['cantidad_liquidacion'], 2) . "\n";
            echo "  Saldo Pendiente: \$" . number_format($item['saldo_pendiente'], 2) . "\n";
            echo "  ┌─ PRESUPUESTO_ITEMS (col4):\n";
            echo "  │  Col4: \$" . number_format($item['col4'], 2) . "\n";
            echo "  │  Saldo Disponible: \$" . number_format($item['saldo_disponible'], 2) . "\n";
            echo "  └─\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Ahora buscar por certificado específico
echo "\n\n═══════════════════════════════════════════════════════════════\n";
echo "🔍 ANÁLISIS POR CERTIFICADO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$query2 = "
SELECT 
    c.id,
    c.numero_certificado,
    c.monto_total,
    c.total_liquidado,
    c.total_pendiente,
    COUNT(dc.id) as total_items
FROM certificados c
LEFT JOIN detalle_certificados dc ON c.id = dc.certificado_id
WHERE c.numero_certificado IN ('CERT-001')
GROUP BY c.id, c.numero_certificado, c.monto_total, c.total_liquidado, c.total_pendiente
";

try {
    $stmt = $db->query($query2);
    $certs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($certs as $cert) {
        echo "Certificado: " . $cert['numero_certificado'] . "\n";
        echo "  Monto Total: \$" . number_format($cert['monto_total'], 2) . "\n";
        echo "  Total Liquidado: \$" . number_format($cert['total_liquidado'], 2) . "\n";
        echo "  Total Pendiente: \$" . number_format($cert['total_pendiente'], 2) . "\n";
        echo "  Total Items: " . $cert['total_items'] . "\n\n";
        
        // Detalles de este certificado
        $query3 = "
        SELECT 
            dc.id,
            dc.codigo_completo,
            dc.monto,
            dc.cantidad_liquidacion,
            (dc.monto - dc.cantidad_liquidacion) as saldo_item
        FROM detalle_certificados dc
        WHERE dc.certificado_id = ?
        ORDER BY dc.id
        ";
        
        $stmt3 = $db->prepare($query3);
        $stmt3->execute([$cert['id']]);
        $detalles = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($detalles as $detalle) {
            echo "  Item: " . $detalle['codigo_completo'] . "\n";
            echo "    Monto: \$" . number_format($detalle['monto'], 2) . "\n";
            echo "    Liquidado: \$" . number_format($detalle['cantidad_liquidacion'], 2) . "\n";
            echo "    Saldo: \$" . number_format($detalle['saldo_item'], 2) . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>
