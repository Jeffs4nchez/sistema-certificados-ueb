<?php
require_once __DIR__ . '/../app/Database.php';

$db = Database::getInstance()->getConnection();

echo "Actividades con datos:\n";
echo "======================\n\n";

// Fuentes por actividad
$stmt = $db->query('
    SELECT DISTINCT f.actividad_id, a.codigo, a.descripcion, COUNT(f.id) as num_fuentes
    FROM fuentes_financiamiento f
    LEFT JOIN actividades a ON f.actividad_id = a.id
    WHERE f.actividad_id IS NOT NULL
    GROUP BY f.actividad_id, a.codigo, a.descripcion
    ORDER BY f.actividad_id
');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    echo "Actividad ID: {$row['actividad_id']}\n";
    echo "  Código: {$row['codigo']}\n";
    echo "  Descripción: {$row['descripcion']}\n";
    echo "  Fuentes: {$row['num_fuentes']}\n";
    
    // Contar ubicaciones
    $stmt2 = $db->prepare('
        SELECT COUNT(u.id) as count FROM ubicaciones u
        INNER JOIN fuentes_financiamiento f ON u.fuente_id = f.id
        WHERE f.actividad_id = ?
    ');
    $stmt2->execute([$row['actividad_id']]);
    $ubg = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "  Ubicaciones: {$ubg['count']}\n";
    
    // Contar items
    $stmt3 = $db->prepare('
        SELECT COUNT(i.id) as count FROM items i
        INNER JOIN ubicaciones u ON i.ubicacion_id = u.id
        INNER JOIN fuentes_financiamiento f ON u.fuente_id = f.id
        WHERE f.actividad_id = ?
    ');
    $stmt3->execute([$row['actividad_id']]);
    $items = $stmt3->fetch(PDO::FETCH_ASSOC);
    echo "  Items: {$items['count']}\n\n";
}
?>
