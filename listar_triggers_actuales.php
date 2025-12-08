<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== TRIGGERS ACTIVOS ACTUALMENTE ===\n\n";

$triggers = $db->query("
    SELECT 
        trigger_name,
        event_object_table,
        event_manipulation,
        action_statement
    FROM information_schema.triggers
    WHERE trigger_schema = 'public'
    ORDER BY event_object_table, trigger_name
")->fetchAll(PDO::FETCH_ASSOC);

echo "TABLA | TRIGGER | EVENTO\n";
echo str_repeat("-", 100) . "\n";

foreach ($triggers as $t) {
    printf("%-30s | %-35s | %-10s\n", 
        $t['event_object_table'],
        $t['trigger_name'],
        $t['event_manipulation']
    );
}

echo "\nTotal: " . count($triggers) . " triggers\n";

// Listar funciones asociadas
echo "\n=== FUNCIONES TRIGGER ===\n\n";

$functions = $db->query("
    SELECT 
        pg_proc.proname as function_name,
        pg_namespace.nspname as schema
    FROM pg_proc
    JOIN pg_namespace ON pg_namespace.oid = pg_proc.pronamespace
    WHERE pg_namespace.nspname = 'public'
    AND (pg_proc.proname LIKE 'fn_trigger%' OR pg_proc.proname LIKE 'fn_%')
    ORDER BY pg_proc.proname
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($functions as $f) {
    echo "   - " . $f['function_name'] . "()\n";
}

?>
