<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== DIAGNOSTICANDO col4 ===\n\n";

// Ver qué certificados hay en ese código
$query = "SELECT 
    c.id,
    c.numero_certificado,
    c.monto_total,
    c.total_liquidado,
    c.total_pendiente
FROM certificados c
JOIN detalle_certificados dc ON c.id = dc.certificado_id
WHERE dc.codigo_completo = '01 00 000 001 003 0200 510601'
ORDER BY c.id DESC";

$stmt = $conn->query($query);
$certs = $stmt->fetchAll();

echo "Certificados en código '01 00 000 001 003 0200 510601':\n";
echo "=====================================================\n\n";

$totalPendiente = 0;
foreach($certs as $cert) {
    echo "ID: {$cert['id']} - {$cert['numero_certificado']}\n";
    echo "  Monto Total: \${$cert['monto_total']}\n";
    echo "  Total Liquidado: \${$cert['total_liquidado']}\n";
    echo "  Total Pendiente: \${$cert['total_pendiente']}\n";
    echo "---\n\n";
    $totalPendiente += $cert['total_pendiente'];
}

echo "\nSuma Total Pendiente esperada: \$$totalPendiente\n";

// Ver el col4 actual
$itemQuery = "SELECT col4, col1, col8 FROM presupuesto_items 
WHERE codigo_completo = '01 00 000 001 003 0200 510601'";

$itemStmt = $conn->query($itemQuery);
$item = $itemStmt->fetch();

echo "\npresupuesto_items:\n";
echo "Col4 actual: \${$item['col4']}\n";
echo "Col1: \${$item['col1']}\n";
echo "Col8: \${$item['col8']}\n";

if($item['col4'] == $totalPendiente) {
    echo "\n✅ Col4 es CORRECTO (=total_pendiente)\n";
} else {
    echo "\n❌ Col4 es INCORRECTO\n";
    echo "   Esperado: \$$totalPendiente\n";
    echo "   Actual: \${$item['col4']}\n";
    echo "   Diferencia: \$" . ($item['col4'] - $totalPendiente) . "\n";
}
?>
