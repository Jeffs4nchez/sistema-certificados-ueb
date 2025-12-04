<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== EJECUTANDO TRIGGERS ===\n\n";

// Leer el archivo de triggers
$sqlContent = file_get_contents(__DIR__ . '/database/create_triggers.sql');

// Dividir por punto y coma y ejecutar cada statement
$statements = array_filter(
    array_map('trim', explode(';', $sqlContent)),
    function($stmt) { return !empty($stmt) && strpos($stmt, '--') !== 0; }
);

foreach($statements as $i => $statement) {
    if (empty(trim($statement))) continue;
    
    try {
        $conn->exec($statement);
        echo "✓ Statement " . ($i+1) . " ejecutado\n";
    } catch (Exception $e) {
        echo "✗ Error en statement " . ($i+1) . ": " . $e->getMessage() . "\n";
    }
}

echo "\n\n=== VERIFICANDO TRIGGERS ===\n\n";

$triggerQuery = "SELECT 
    trigger_name, 
    event_manipulation,
    event_object_table
FROM information_schema.triggers 
WHERE trigger_schema = 'public'
AND event_object_table IN ('detalle_certificados', 'presupuesto_items')
ORDER BY event_object_table, trigger_name";

$stmt = $conn->query($triggerQuery);
$triggers = $stmt->fetchAll();

echo "Total de triggers: " . count($triggers) . "\n\n";
foreach($triggers as $trigger) {
    echo "✓ " . $trigger['trigger_name'] . " (" . $trigger['event_manipulation'] . " en " . $trigger['event_object_table'] . ")\n";
}
?>
