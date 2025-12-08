<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  Obtener definición del trigger antiguo\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    // Obtener el SQL del trigger
    $stmt = $db->query("
        SELECT routine_definition
        FROM information_schema.routines
        WHERE routine_schema NOT IN ('pg_catalog', 'information_schema')
        AND routine_type = 'FUNCTION'
        ORDER BY routine_name
    ");
    
    $functions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($functions as $func) {
        echo $func['routine_definition'] . "\n\n";
        echo "════════════════════════════════════════════════════════════════\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
