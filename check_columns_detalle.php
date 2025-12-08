<?php
require 'app/Database.php';

$db = Database::getInstance()->getConnection();

// PostgreSQL
$stmt = $db->query("
    SELECT column_name, data_type, column_default 
    FROM information_schema.columns 
    WHERE table_name = 'detalle_certificados' 
    ORDER BY ordinal_position
");

echo "Columnas en detalle_certificados:\n";
echo str_repeat("=", 60) . "\n";

foreach ($stmt->fetchAll() as $col) {
    $default = $col['column_default'] ? " DEFAULT " . $col['column_default'] : '';
    echo $col['column_name'] . " (" . $col['data_type'] . ")" . $default . "\n";
}
?>
