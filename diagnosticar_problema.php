<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== DIAGNOSTICANDO PROBLEMA DE ELIMINACIÓN ===\n\n";

// Ver todos los presupuesto_items que tengan col4 negativa o con problemas
$query = "SELECT id, codigo_item, col1, col4, col7, col8 
FROM presupuesto_items 
WHERE col4 < 0 OR col4 IS NULL 
ORDER BY id DESC
LIMIT 10";

$stmt = $conn->query($query);
$problematicItems = $stmt->fetchAll();

echo "Items con col4 negativa o NULL:\n";
echo "================================\n\n";

foreach($problematicItems as $item) {
    echo "ID: {$item['id']}\n";
    echo "Código: {$item['codigo_item']}\n";
    echo "Col1 (Disponible): " . number_format($item['col1'], 2) . "\n";
    echo "Col4 (Total Certificado): " . number_format($item['col4'], 2) . " ← PROBLEMA\n";
    echo "Col7 (Total Liquidado): " . number_format($item['col7'], 2) . "\n";
    echo "Col8 (Saldo): " . number_format($item['col8'], 2) . "\n";
    echo "---\n";
}

echo "\n\n=== REVISANDO ITEMS CERTIFICADOS ELIMINADOS ===\n\n";

// Ver si hay certificados con items que no existen
$query2 = "SELECT 
    pi.id,
    pi.codigo_item,
    COUNT(dc.id) as detalle_count,
    pi.col4,
    pi.col7
FROM presupuesto_items pi
LEFT JOIN detalle_certificados dc ON pi.codigo_item = dc.codigo_completo
GROUP BY pi.id, pi.codigo_item, pi.col4, pi.col7
HAVING COUNT(dc.id) = 0 AND (pi.col4 != 0 OR pi.col7 != 0)
LIMIT 10";

$stmt2 = $conn->query($query2);
$orphaned = $stmt2->fetchAll();

echo "Items de presupuesto sin detalle_certificados pero con col4/col7:\n";
echo count($orphaned) . " encontrados\n\n";

foreach($orphaned as $item) {
    echo "Código: {$item['codigo_item']}\n";
    echo "Col4: " . number_format($item['col4'], 2) . "\n";
    echo "Col7: " . number_format($item['col7'], 2) . "\n";
    echo "---\n";
}

echo "\n\n=== REVISANDO TRIGGERS DUPLICADOS ===\n\n";

$triggerQuery = "SELECT 
    trigger_name,
    event_object_table,
    action_timing,
    event_manipulation
FROM information_schema.triggers 
WHERE trigger_schema = 'public'
AND event_object_table = 'detalle_certificados'
AND event_manipulation = 'DELETE'
ORDER BY trigger_name";

$stmt3 = $conn->query($triggerQuery);
$deleteTriggers = $stmt3->fetchAll();

echo "Triggers DELETE en detalle_certificados:\n";
foreach($deleteTriggers as $trigger) {
    echo "- {$trigger['trigger_name']} ({$trigger['action_timing']})\n";
}

if(count($deleteTriggers) > 1) {
    echo "\n⚠️  PROBLEMA: Hay MÚLTIPLES triggers DELETE!\n";
    echo "Ambos se ejecutan, causando restar DOS VECES el monto\n";
}
?>
