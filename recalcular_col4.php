<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== RECALCULANDO col4 Y col8 ===\n\n";

// Paso 1: Obtener todos los presupuesto_items que tienen certificados
$query = "SELECT DISTINCT 
    pi.id,
    pi.codigo_completo,
    pi.col1
FROM presupuesto_items pi
WHERE EXISTS (
    SELECT 1 FROM detalle_certificados dc 
    WHERE dc.codigo_completo = pi.codigo_completo
)";

$stmt = $conn->query($query);
$items = $stmt->fetchAll();

echo "Encontrados " . count($items) . " items con certificados\n\n";

$updated = 0;
foreach($items as $item) {
    // Calcular col4 = SUM(total_pendiente)
    $calcQuery = "SELECT COALESCE(SUM(c.total_pendiente), 0) as total_pendiente
    FROM certificados c
    WHERE EXISTS (
        SELECT 1 FROM detalle_certificados dc 
        WHERE dc.certificado_id = c.id 
        AND dc.codigo_completo = ?
    )";
    
    $calcStmt = $conn->prepare($calcQuery);
    $calcStmt->execute([$item['codigo_completo']]);
    $calc = $calcStmt->fetch();
    
    $newCol4 = $calc['total_pendiente'];
    $newCol8 = $item['col1'] - $newCol4;
    
    // Actualizar
    $updateStmt = $conn->prepare("UPDATE presupuesto_items 
    SET col4 = ?, col8 = ?, fecha_actualizacion = NOW()
    WHERE id = ?");
    $updateStmt->execute([$newCol4, $newCol8, $item['id']]);
    
    $updated++;
    
    if($updated % 10 == 0) {
        echo "✓ Procesados $updated items\n";
    }
}

echo "\n✅ Total actualizado: $updated items\n\n";

// Verificación
echo "=== VERIFICANDO RESULTADOS ===\n\n";

$verifyQuery = "SELECT 
    codigo_completo,
    col1,
    col4,
    col8,
    (SELECT COALESCE(SUM(total_pendiente), 0) FROM certificados c 
     WHERE EXISTS (SELECT 1 FROM detalle_certificados WHERE certificado_id = c.id AND codigo_completo = pi.codigo_completo)) as expected_col4
FROM presupuesto_items pi
WHERE col4 > 0 OR col8 != col1
ORDER BY col4 DESC
LIMIT 10";

$verifyStmt = $conn->query($verifyQuery);
$results = $verifyStmt->fetchAll();

echo "Top 10 items con certificados:\n";
echo "================================\n\n";

$allCorrect = true;
foreach($results as $result) {
    $status = ($result['col4'] == $result['expected_col4']) ? "✓" : "✗";
    echo "$status Código: {$result['codigo_completo']}\n";
    echo "   Col1 (Disponible): \${$result['col1']}\n";
    echo "   Col4 (Pendiente): \${$result['col4']}\n";
    echo "   Col8 (Saldo): \${$result['col8']}\n";
    
    if($result['col4'] != $result['expected_col4']) {
        echo "   ERROR: Expected col4 = \${$result['expected_col4']}\n";
        $allCorrect = false;
    }
    echo "\n";
}

if($allCorrect) {
    echo "\n✅ TODOS LOS VALORES SON CORRECTOS\n";
} else {
    echo "\n⚠️  Hay discrepancias\n";
}
?>
