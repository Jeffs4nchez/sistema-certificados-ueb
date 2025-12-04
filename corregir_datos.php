<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== CORRIGIENDO DATOS DAÑADOS ===\n\n";

// Primero, mostrar items con problemas
$query = "SELECT id, codigo_completo, col4, col7 FROM presupuesto_items 
WHERE col4 < 0 OR col4 IS NULL OR col7 < 0 OR col7 IS NULL
ORDER BY id";

$stmt = $conn->query($query);
$problemItems = $stmt->fetchAll();

echo "Items con datos inconsistentes:\n";
echo count($problemItems) . " encontrados\n\n";

// Ahora recalcular cada uno
foreach($problemItems as $item) {
    $codigo = $item['codigo_completo'];
    
    // Calcular nuevos valores
    $calcQuery = "SELECT 
        COALESCE(SUM(monto), 0) as total_monto,
        COALESCE(SUM(cantidad_liquidacion), 0) as total_liquidado
    FROM detalle_certificados
    WHERE codigo_completo = ?";
    
    $calcStmt = $conn->prepare($calcQuery);
    $calcStmt->execute([$codigo]);
    $calc = $calcStmt->fetch();
    
    $newCol4 = $calc['total_monto'];
    $newCol7 = $calc['total_liquidado'];
    
    // Obtener col1, col5, col6 para calcular col8
    $infoQuery = "SELECT col1, col5, col6 FROM presupuesto_items WHERE codigo_completo = ?";
    $infoStmt = $conn->prepare($infoQuery);
    $infoStmt->execute([$codigo]);
    $info = $infoStmt->fetch();
    
    $newCol8 = ($info['col1'] ?? 0) - $newCol4 - ($info['col5'] ?? 0) - ($info['col6'] ?? 0) - $newCol7;
    
    // Actualizar
    $updateQuery = "UPDATE presupuesto_items 
    SET col4 = ?, col7 = ?, col8 = ?, fecha_actualizacion = NOW()
    WHERE codigo_completo = ?";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([$newCol4, $newCol7, $newCol8, $codigo]);
    
    echo "✓ Código: " . $codigo . "\n";
    echo "  Col4: " . $item['col4'] . " → " . $newCol4 . "\n";
    echo "  Col7: " . $item['col7'] . " → " . $newCol7 . "\n";
    echo "  Col8 calculado: " . $newCol8 . "\n";
    echo "---\n";
}

echo "\n\n=== VERIFICANDO RESULTADOS ===\n\n";

$finalQuery = "SELECT id, codigo_completo, col4, col7, col8 FROM presupuesto_items 
WHERE col4 < 0 OR col7 < 0 OR col8 > 99999999
ORDER BY id";

$finalStmt = $conn->query($finalQuery);
$stillProblems = $finalStmt->fetchAll();

if(count($stillProblems) == 0) {
    echo "✓ Todos los datos han sido corregidos correctamente!\n";
} else {
    echo "⚠️  Aún hay " . count($stillProblems) . " items con problemas\n";
    foreach($stillProblems as $item) {
        echo "  - Código: " . $item['codigo_completo'] . " (col4=" . $item['col4'] . ", col7=" . $item['col7'] . ")\n";
    }
}
?>
