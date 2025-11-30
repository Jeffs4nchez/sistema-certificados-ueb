<?php
require_once __DIR__ . '/../app/Database.php';

$db = Database::getInstance()->getConnection();

// Buscar actividad con código 002
$stmt = $db->prepare('SELECT * FROM actividades WHERE codigo = ?');
$stmt->execute(['002']);
$actividad = $stmt->fetch(PDO::FETCH_ASSOC);

if ($actividad) {
    echo "Actividad encontrada:\n";
    print_r($actividad);
    
    // Ahora buscar fuentes para esta actividad
    $stmt = $db->prepare('SELECT * FROM fuentes_financiamiento WHERE actividad_id = ?');
    $stmt->execute([$actividad['id']]);
    $fuentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nFuentes de esta actividad: " . count($fuentes) . "\n";
    foreach ($fuentes as $f) {
        echo "  - {$f['codigo']}: {$f['descripcion']} (ID: {$f['id']})\n";
    }
} else {
    echo "Actividad con código 002 no encontrada\n";
}
?>
