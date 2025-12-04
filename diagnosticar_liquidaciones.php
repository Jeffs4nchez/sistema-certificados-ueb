<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== DIAGNOSTICANDO LIQUIDACIONES ===\n\n";

// Ver todas las liquidaciones
$query = "SELECT 
    dc.id,
    dc.certificado_id,
    dc.codigo_completo,
    dc.monto,
    dc.cantidad_liquidacion,
    dc.memorando,
    c.numero_certificado,
    c.monto_total,
    c.total_liquidado
FROM detalle_certificados dc
JOIN certificados c ON dc.certificado_id = c.id
ORDER BY dc.id DESC
LIMIT 10";

$stmt = $conn->query($query);
$items = $stmt->fetchAll();

echo "Últimos 10 items de certificados:\n";
echo "==================================\n\n";

foreach($items as $item) {
    echo "Item ID: {$item['id']}\n";
    echo "Certificado: {$item['numero_certificado']}\n";
    echo "Código Item: {$item['codigo_completo']}\n";
    echo "Monto: \${$item['monto']}\n";
    echo "Cantidad Liquidación: \${$item['cantidad_liquidacion']}\n";
    echo "Memorando: {$item['memorando']}\n";
    echo "---\n\n";
}

echo "\n=== VERIFICANDO presupuesto_items ===\n\n";

// Ver si hay presupuesto_items con col7 (liquidado)
$query2 = "SELECT 
    id,
    codigo_completo,
    col4,
    col7,
    col8
FROM presupuesto_items 
WHERE col7 > 0 OR col4 > 0
ORDER BY col7 DESC, col4 DESC
LIMIT 10";

$stmt2 = $conn->query($query2);
$presupItems = $stmt2->fetchAll();

echo "Items de presupuesto con movimientos:\n";
echo "====================================\n\n";

foreach($presupItems as $item) {
    echo "Código: {$item['codigo_completo']}\n";
    echo "Col4 (Total Certificado): \${$item['col4']}\n";
    echo "Col7 (Total Liquidado): \${$item['col7']}\n";
    echo "Col8 (Saldo): \${$item['col8']}\n";
    echo "---\n\n";
}

echo "\n=== COMPARANDO ===\n\n";

// Comparar: si hay liquidación en detalle_certificados pero no en presupuesto_items
$query3 = "SELECT 
    dc.codigo_completo,
    SUM(dc.monto) as total_monto,
    SUM(dc.cantidad_liquidacion) as total_liquidado,
    pi.col4,
    pi.col7
FROM detalle_certificados dc
LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
GROUP BY dc.codigo_completo, pi.col4, pi.col7
HAVING SUM(dc.cantidad_liquidacion) > 0
ORDER BY SUM(dc.cantidad_liquidacion) DESC";

$stmt3 = $conn->query($query3);
$comparisons = $stmt3->fetchAll();

echo "Items que DEBERÍAN tener col7 > 0:\n";
foreach($comparisons as $comp) {
    $expected = $comp['total_liquidado'];
    $actual = $comp['col7'] ?? 0;
    $status = ($expected == $actual) ? "✓" : "✗";
    
    echo "$status Código: {$comp['codigo_completo']}\n";
    echo "   Esperado col7: \${$expected}, Actual: \${$actual}\n";
    if($expected != $actual) {
        echo "   DIFERENCIA: \$" . ($expected - $actual) . "\n";
    }
    echo "\n";
}
?>
