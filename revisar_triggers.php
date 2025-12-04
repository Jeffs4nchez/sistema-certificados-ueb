<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== TODOS LOS TRIGGERS ===\n\n";

$triggerQuery = "SELECT 
    trigger_name, 
    event_manipulation,
    event_object_table
FROM information_schema.triggers 
WHERE trigger_schema = 'certificados_db'";

$stmt = $conn->query($triggerQuery);
$triggers = $stmt->fetchAll();

echo "Total de triggers encontrados: " . count($triggers) . "\n\n";
foreach($triggers as $trigger) {
    echo "- " . $trigger['trigger_name'] . " (" . $trigger['event_manipulation'] . " en " . $trigger['event_object_table'] . ")\n";
}

// Ahora obtener los detalles del trigger de delete
echo "\n\n=== DETALLES DEL TRIGGER trigger_delete_detalle_certificados ===\n\n";

$detailQuery = "SELECT routine_definition FROM information_schema.routines 
WHERE routine_name = 'trigger_delete_detalle_certificados'
AND routine_schema = 'certificados_db'";

$stmt2 = $conn->query($detailQuery);
$detail = $stmt2->fetch();

if($detail) {
    echo $detail['routine_definition'];
} else {
    echo "No se encontró la función";
}
?>
