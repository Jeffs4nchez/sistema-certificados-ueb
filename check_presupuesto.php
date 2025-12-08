<?php
require_once 'app/Database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT * FROM presupuesto_items 
    WHERE codigo_completo = ? OR codigo_completo LIKE ?
");
$stmt->execute(['01 00 001 002 001 0200 510203', '%510203%']);

$results = $stmt->fetchAll();

echo "Registros en presupuesto_items para código 01 00 001 002 001 0200 510203:\n";
echo "Total encontrados: " . count($results) . "\n\n";

foreach ($results as $row) {
    echo "ID: " . $row['id'] . "\n";
    echo "Código: " . $row['codigo_completo'] . "\n";
    echo "col4: " . $row['col4'] . "\n";
    echo "---\n";
}
?>
