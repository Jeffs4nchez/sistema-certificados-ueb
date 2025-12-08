<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query('SELECT * FROM presupuesto_items LIMIT 1');
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if($result) {
    echo "Columnas en presupuesto_items:\n";
    foreach(array_keys($result) as $col) {
        echo "  - $col\n";
    }
} else {
    echo "No hay registros en presupuesto_items\n";
}
?>
