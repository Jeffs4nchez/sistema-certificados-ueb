<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

// Ver estructura de presupuesto_items
$query = "SELECT column_name, data_type FROM information_schema.columns 
WHERE table_name = 'presupuesto_items' AND table_schema = 'public'";

$stmt = $conn->query($query);
$columns = $stmt->fetchAll();

echo "=== COLUMNAS DE presupuesto_items ===\n\n";
foreach($columns as $col) {
    echo "- " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
}
?>
