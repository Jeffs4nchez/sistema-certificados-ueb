<?php
require_once 'app/Database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->query('
    SELECT DISTINCT f.actividad_id, a.codigo, a.descripcion, COUNT(f.id) as count
    FROM fuentes_financiamiento f
    LEFT JOIN actividades a ON f.actividad_id = a.id
    GROUP BY f.actividad_id, a.codigo, a.descripcion
    ORDER BY f.actividad_id
');
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Actividades con datos:\n";
echo "======================\n\n";
foreach ($result as $row) {
    echo "Actividad ID: " . $row['actividad_id'] . "\n";
    echo "  Código: " . $row['codigo'] . "\n";
    echo "  Descripción: " . $row['descripcion'] . "\n";
    echo "  Fuentes: " . $row['count'] . "\n\n";
}
?>
