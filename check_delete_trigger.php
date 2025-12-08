<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  Verificar Estado de Triggers en BD                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Ver todos los triggers
    $stmt = $db->query("
        SELECT trigger_name, event_object_table, event_manipulation, action_timing
        FROM information_schema.triggers
        WHERE trigger_schema NOT IN ('pg_catalog', 'information_schema')
        ORDER BY event_object_table, trigger_name
    ");
    
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Triggers activos:\n";
    foreach ($triggers as $t) {
        echo "  - {$t['trigger_name']} ({$t['action_timing']} {$t['event_manipulation']}) en {$t['event_object_table']}\n";
    }
    
    echo "\n\nDetalles del trigger DELETE:\n";
    echo "═══════════════════════════════════════════════════════════\n";
    
    // Obtener el código del trigger
    $stmt = $db->query("
        SELECT routine_name, routine_definition
        FROM information_schema.routines
        WHERE routine_name = 'fn_trigger_detalle_delete_col4'
    ");
    
    $func = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($func) {
        echo $func['routine_definition'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
