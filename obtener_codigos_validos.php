<?php
/**
 * Obtener códigos válidos de presupuesto_items
 */

require_once 'app/Database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->query("
    SELECT id, codigo_completo, col4 
    FROM presupuesto_items 
    LIMIT 5
");

$rows = $stmt->fetchAll();

echo "Códigos disponibles en presupuesto_items:\n\n";

foreach ($rows as $row) {
    echo "ID: {$row['id']}\n";
    echo "Código: {$row['codigo_completo']}\n";
    echo "Col4: {$row['col4']}\n\n";
}
?>
