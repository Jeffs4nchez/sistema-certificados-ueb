<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== CORRIGIENDO col4 NEGATIVOS ===\n\n";

// Recalcular todos los col4 basándose en certificados actuales
$query = "SELECT DISTINCT pi.id, pi.codigo_completo, pi.col1
FROM presupuesto_items pi
LEFT JOIN detalle_certificados dc ON pi.codigo_completo = dc.codigo_completo
WHERE pi.col4 < 0 OR pi.col4 IS NULL";

$stmt = $conn->query($query);
$items = $stmt->fetchAll();

echo "Items con col4 negativa o NULL: " . count($items) . "\n\n";

foreach($items as $item) {
    // Calcular col4 correcto
    $calcQuery = "SELECT COALESCE(SUM(c.total_pendiente), 0) as total_pend
    FROM certificados c
    INNER JOIN detalle_certificados dc ON c.id = dc.certificado_id
    WHERE dc.codigo_completo = ?";
    
    $calcStmt = $conn->prepare($calcQuery);
    $calcStmt->execute([$item['codigo_completo']]);
    $calc = $calcStmt->fetch();
    
    $newCol4 = $calc['total_pend'];
    $newCol8 = $item['col1'] - $newCol4;
    
    // Actualizar
    $updateStmt = $conn->prepare("UPDATE presupuesto_items 
    SET col4 = ?, col8 = ?, fecha_actualizacion = NOW()
    WHERE id = ?");
    $updateStmt->execute([$newCol4, $newCol8, $item['id']]);
    
    echo "✓ Código {$item['codigo_completo']}\n";
    echo "  Col4: " . ($newCol4 >= 0 ? "\$$newCol4" : "-\$$newCol4") . "\n\n";
}

echo "\n✅ Corrección completada\n";
?>
