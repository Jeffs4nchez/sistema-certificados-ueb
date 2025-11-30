<?php
require_once 'app/Database.php';
$db = Database::getInstance()->getConnection();

echo "Buscando el path correcto hasta Actividad 001 con datos...\n";
echo "=========================================================\n\n";

// Actividad 36 (001 - ADMINISTRACION) tiene los datos
$stmt = $db->prepare('
    SELECT a.id, a.codigo, a.descripcion, a.proyecto_id
    FROM actividades a
    WHERE a.id = 36
');
$stmt->execute();
$actividad = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Actividad objetivo: {$actividad['codigo']} - {$actividad['descripcion']}\n";
echo "Actividad ID: {$actividad['id']}\n";
echo "Proyecto ID: {$actividad['proyecto_id']}\n\n";

// Seguir hacia atrás
$proyectoId = $actividad['proyecto_id'];
$stmt = $db->prepare('SELECT * FROM proyectos WHERE id = ?');
$stmt->execute([$proyectoId]);
$proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Proyecto: {$proyecto['codigo']} - {$proyecto['descripcion']}\n";
echo "Subprograma ID: {$proyecto['subprograma_id']}\n\n";

$subprogramaId = $proyecto['subprograma_id'];
$stmt = $db->prepare('SELECT * FROM subprogramas WHERE id = ?');
$stmt->execute([$subprogramaId]);
$subprograma = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Subprograma: {$subprograma['codigo']} - {$subprograma['descripcion']}\n";
echo "Programa ID: {$subprograma['programa_id']}\n\n";

$programaId = $subprograma['programa_id'];
$stmt = $db->prepare('SELECT * FROM programas WHERE id = ?');
$stmt->execute([$programaId]);
$programa = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Programa: {$programa['codigo']} - {$programa['descripcion']}\n\n";

echo "========== PATH CORRECTO ==========\n";
echo "Programa: " . $programa['codigo'] . " ({$programa['id']})\n";
echo "  → Subprograma: " . $subprograma['codigo'] . " ({$subprograma['id']})\n";
echo "    → Proyecto: " . $proyecto['codigo'] . " ({$proyecto['id']})\n";
echo "      → Actividad: " . $actividad['codigo'] . " ({$actividad['id']}) ← TIENE DATOS\n";
?>
