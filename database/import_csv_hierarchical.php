<?php
// Script para importar CSV y crear jerarquía completa
require_once '../app/Database.php';
$db = Database::getInstance()->getConnection();
$db->exec("SET search_path TO public;");

$csvFile = __DIR__ . '/items.csv';
if (!file_exists($csvFile)) {
    die('No se encontró el archivo CSV.');
}

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ';');

while (($row = fgetcsv($handle, 0, ';')) !== false) {
    // Extraer campos
    $codPrograma = trim($row[0]);
    $descPrograma = trim($row[1]);
    $codSubprograma = trim($row[2]);
    $descSubprograma = trim($row[3]);
    $codProyecto = trim($row[4]);
    $descProyecto = trim($row[5]);
    $codActividad = trim($row[6]);
    $descActividad = trim($row[7]);
    $codItem = trim($row[8]);
    $descItem = trim($row[9]);
    $codUbicacion = trim($row[10]);
    $descUbicacion = trim($row[11]);
    $codFuente = trim($row[12]);
    $descFuente = trim($row[13]);

    // Insertar o buscar Programa
    $stmt = $db->prepare('SELECT id FROM programas WHERE codigo = ?');
    $stmt->execute([$codPrograma]);
    $idPrograma = $stmt->fetchColumn();
    if (!$idPrograma) {
        $stmt = $db->prepare('INSERT INTO programas (codigo, descripcion) VALUES (?, ?)');
        $stmt->execute([$codPrograma, $descPrograma]);
           $idPrograma = $db->query("SELECT currval('programas_id_seq')")->fetchColumn();
    }

    // Insertar o buscar Subprograma
    $stmt = $db->prepare('SELECT id FROM subprogramas WHERE codigo = ? AND programa_id = ?');
    $stmt->execute([$codSubprograma, $idPrograma]);
    $idSubprograma = $stmt->fetchColumn();
    if (!$idSubprograma) {
        $stmt = $db->prepare('INSERT INTO subprogramas (codigo, descripcion, programa_id) VALUES (?, ?, ?)');
        $stmt->execute([$codSubprograma, $descSubprograma, $idPrograma]);
           $idSubprograma = $db->query("SELECT currval('subprogramas_id_seq')")->fetchColumn();
    }

    // Insertar o buscar Proyecto
    $stmt = $db->prepare('SELECT id FROM proyectos WHERE codigo = ? AND subprograma_id = ?');
    $stmt->execute([$codProyecto, $idSubprograma]);
    $idProyecto = $stmt->fetchColumn();
    if (!$idProyecto) {
        $stmt = $db->prepare('INSERT INTO proyectos (codigo, descripcion, subprograma_id) VALUES (?, ?, ?)');
        $stmt->execute([$codProyecto, $descProyecto, $idSubprograma]);
           $idProyecto = $db->query("SELECT currval('proyectos_id_seq')")->fetchColumn();
    }

    // Insertar o buscar Actividad
    $stmt = $db->prepare('SELECT id FROM actividades WHERE codigo = ? AND proyecto_id = ?');
    $stmt->execute([$codActividad, $idProyecto]);
    $idActividad = $stmt->fetchColumn();
    if (!$idActividad) {
        $stmt = $db->prepare('INSERT INTO actividades (codigo, descripcion, proyecto_id) VALUES (?, ?, ?)');
        $stmt->execute([$codActividad, $descActividad, $idProyecto]);
           $idActividad = $db->query("SELECT currval('actividades_id_seq')")->fetchColumn();
    }

    // Insertar o buscar Fuente
    $stmt = $db->prepare('SELECT id FROM fuentes_financiamiento WHERE codigo = ? AND actividad_id = ?');
    $stmt->execute([$codFuente, $idActividad]);
    $idFuente = $stmt->fetchColumn();
    if (!$idFuente) {
        $stmt = $db->prepare('INSERT INTO fuentes_financiamiento (codigo, descripcion, actividad_id) VALUES (?, ?, ?)');
        $stmt->execute([$codFuente, $descFuente, $idActividad]);
           $idFuente = $db->query("SELECT currval('fuentes_financiamiento_id_seq')")->fetchColumn();
    }

    // Insertar o buscar Ubicación
    $stmt = $db->prepare('SELECT id FROM ubicaciones WHERE codigo = ? AND fuente_id = ?');
    $stmt->execute([$codUbicacion, $idFuente]);
    $idUbicacion = $stmt->fetchColumn();
    if (!$idUbicacion) {
        $stmt = $db->prepare('INSERT INTO ubicaciones (codigo, descripcion, fuente_id) VALUES (?, ?, ?)');
        $stmt->execute([$codUbicacion, $descUbicacion, $idFuente]);
           $idUbicacion = $db->query("SELECT currval('ubicaciones_id_seq')")->fetchColumn();
    }

    // Insertar Item
    $stmt = $db->prepare('SELECT id FROM items WHERE codigo = ? AND ubicacion_id = ?');
    $stmt->execute([$codItem, $idUbicacion]);
    $idItem = $stmt->fetchColumn();
    if (!$idItem) {
        $stmt = $db->prepare('INSERT INTO items (codigo, descripcion, ubicacion_id) VALUES (?, ?, ?)');
        $stmt->execute([$codItem, $descItem, $idUbicacion]);
    }
}
fclose($handle);
echo "Importación completada.\n";
?>
