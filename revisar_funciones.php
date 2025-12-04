<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== REVISANDO TODAS LAS FUNCIONES DE TRIGGERS ===\n\n";

$query = "SELECT routine_name, routine_definition 
FROM information_schema.routines 
WHERE routine_type = 'FUNCTION'
AND routine_schema = 'public'
AND routine_name LIKE 'trigger%'
ORDER BY routine_name";

$stmt = $conn->query($query);
$functions = $stmt->fetchAll();

foreach($functions as $func) {
    echo "FUNCIÓN: " . $func['routine_name'] . "\n";
    echo "==========================================\n";
    echo $func['routine_definition'];
    echo "\n\n";
}

echo "\n=== OTROS TRIGGERS (NO trigger_) ===\n\n";

$query2 = "SELECT routine_name, routine_definition 
FROM information_schema.routines 
WHERE routine_type = 'FUNCTION'
AND routine_schema = 'public'
AND routine_name NOT LIKE 'trigger%'
AND routine_name NOT LIKE 'pg_%'
ORDER BY routine_name";

$stmt2 = $conn->query($query2);
$functions2 = $stmt2->fetchAll();

foreach($functions2 as $func) {
    echo "FUNCIÓN: " . $func['routine_name'] . "\n";
    echo "==========================================\n";
    echo $func['routine_definition'];
    echo "\n\n";
}
?>
