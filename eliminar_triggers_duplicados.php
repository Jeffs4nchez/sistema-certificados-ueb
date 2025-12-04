<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== ELIMINANDO TRIGGERS DUPLICADOS ===\n\n";

// Listar los triggers a eliminar
$triggersToDelete = [
    'trg_detalle_delete',
    'trg_detalle_insert',
    'trg_detalle_update'
];

foreach($triggersToDelete as $trigger) {
    try {
        $sql = "DROP TRIGGER IF EXISTS " . $trigger . " ON detalle_certificados CASCADE";
        $conn->exec($sql);
        echo "✓ Trigger " . $trigger . " eliminado\n";
    } catch (Exception $e) {
        echo "✗ Error eliminando " . $trigger . ": " . $e->getMessage() . "\n";
    }
}

echo "\n=== ELIMINANDO FUNCIONES DUPLICADAS ===\n\n";

// Listar las funciones a eliminar
$functionsToDelete = [
    'detalle_certificados_delete',
    'detalle_certificados_insert',
    'detalle_certificados_update'
];

foreach($functionsToDelete as $func) {
    try {
        $sql = "DROP FUNCTION IF EXISTS " . $func . "() CASCADE";
        $conn->exec($sql);
        echo "✓ Función " . $func . "() eliminada\n";
    } catch (Exception $e) {
        echo "✗ Error eliminando " . $func . ": " . $e->getMessage() . "\n";
    }
}

echo "\n\n=== VERIFICANDO TRIGGERS RESTANTES ===\n\n";

$query = "SELECT trigger_name, event_manipulation, event_object_table
FROM information_schema.triggers 
WHERE trigger_schema = 'public'
AND event_object_table = 'detalle_certificados'
ORDER BY trigger_name";

$stmt = $conn->query($query);
$triggers = $stmt->fetchAll();

echo "Total de triggers en detalle_certificados: " . count($triggers) . "\n\n";
foreach($triggers as $trigger) {
    echo "✓ " . $trigger['trigger_name'] . " (" . $trigger['event_manipulation'] . ")\n";
}
?>
