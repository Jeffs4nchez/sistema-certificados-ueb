<?php
require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "ACTIVIDADES:\n";
    echo str_repeat("=", 80) . "\n";
    $stmt = $db->query('SELECT id, codigo, descripcion FROM actividades ORDER BY id');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
        echo sprintf("ID: %3d | Código: %s | %s\n", $a['id'], $a['codigo'], $a['descripcion']);
    }
    
    echo "\nFUENTES:\n";
    echo str_repeat("=", 80) . "\n";
    $stmt = $db->query('SELECT id, codigo, descripcion, actividad_id FROM fuentes_financiamiento ORDER BY id');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
        echo sprintf("ID: %3d | Código: %s | Actividad ID: %3d | %s\n", 
            $f['id'], $f['codigo'], $f['actividad_id'], $f['descripcion']);
    }
    
    echo "\nRELACIONES FUENTE → ACTIVIDAD:\n";
    echo str_repeat("=", 80) . "\n";
    $stmt = $db->query('
        SELECT f.id, f.codigo as fuente_codigo, f.descripcion as fuente_desc,
               a.id as act_id, a.codigo as act_codigo, a.descripcion as act_desc
        FROM fuentes_financiamiento f
        JOIN actividades a ON f.actividad_id = a.id
        ORDER BY f.actividad_id, f.id
    ');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo sprintf("Fuente %d (Código: %s) → Actividad %d (Código: %s)\n", 
            $row['id'], $row['fuente_codigo'], $row['act_id'], $row['act_codigo']);
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
