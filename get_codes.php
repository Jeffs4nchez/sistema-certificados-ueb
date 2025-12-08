<?php
require_once 'app/Database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->query("SELECT id, codigo_completo, col4 FROM presupuesto_items LIMIT 3");

foreach($stmt->fetchAll() as $row) {
    echo $row['codigo_completo'] . "\n";
}
?>
