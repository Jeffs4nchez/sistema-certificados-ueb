<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== ELIMINANDO TRIGGER DUPLICADO ===\n\n";

// El problema: trigger_insert_detalle_certificados suma col4
// La solución: Solo necesitamos el sync_col4_from_certificates que reemplaza

echo "Eliminando trigger_insert_detalle_certificados...\n";
echo "(No es necesario porque sync_col4_from_certificates ya lo hace)\n\n";

try {
    $conn->exec("DROP TRIGGER IF EXISTS trigger_insert_detalle_certificados ON detalle_certificados CASCADE");
    echo "✓ Trigger eliminado\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "También eliminar trigger_update_liquidacion si duplica...\n";
try {
    // Ver si el UPDATE es duplicado
    $query = "SELECT routine_definition FROM information_schema.routines 
    WHERE routine_type = 'FUNCTION'
    AND routine_name = 'trigger_update_liquidacion'";
    
    $stmt = $conn->query($query);
    $func = $stmt->fetch();
    
    if(strpos($func['routine_definition'], 'col7') !== false) {
        echo "✓ trigger_update_liquidacion actualiza col7 en presupuesto_items\n";
        echo "  Esto es correcto, se mantiene\n\n";
    }
} catch (Exception $e) {
    echo "- Saltado\n\n";
}

echo "\n=== VERIFICANDO TRIGGERS FINALES ===\n\n";

$query = "SELECT trigger_name, event_manipulation, action_timing
FROM information_schema.triggers 
WHERE trigger_schema = 'public'
AND event_object_table IN ('detalle_certificados', 'certificados')
ORDER BY event_object_table, trigger_name";

$stmt = $conn->query($query);
$triggers = $stmt->fetchAll();

echo "Triggers de detalle_certificados:\n";
foreach($triggers as $trigger) {
    if($trigger['event_object_table'] == 'detalle_certificados') {
        echo "- {$trigger['trigger_name']} ({$trigger['action_timing']} {$trigger['event_manipulation']})\n";
    }
}

echo "\nTriggers de certificados:\n";
foreach($triggers as $trigger) {
    if($trigger['event_object_table'] == 'certificados') {
        echo "- {$trigger['trigger_name']} ({$trigger['action_timing']} {$trigger['event_manipulation']})\n";
    }
}

echo "\n\n✅ Configuración de triggers optimizada\n";
?>
