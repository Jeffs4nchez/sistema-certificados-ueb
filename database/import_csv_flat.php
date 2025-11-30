<?php
// Importa el CSV a la tabla estructura_presupuestaria
require_once '../app/Database.php';
$db = Database::getInstance()->getConnection();
$db->exec("SET search_path TO public;");

// Ruta del archivo CSV
$csvFile = __DIR__ . '/items.csv';
if (!file_exists($csvFile)) {
    die('No se encontró el archivo CSV.');
}

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ';'); // Ignorar encabezado

while (($row = fgetcsv($handle, 0, ';')) !== false) {
    // Concatenar el código completo
    $codigo_completo = trim($row[0]) . '-' . trim($row[2]) . '-' . trim($row[4]) . '-' . trim($row[6]) . '-' . trim($row[8]) . '-' . trim($row[10]) . '-' . trim($row[12]) . '-' . trim($row[14]) . '-' . trim($row[16]);
    $stmt = $db->prepare('INSERT INTO estructura_presupuestaria (
        cod_programa, desc_programa,
        cod_subprograma, desc_subprograma,
        cod_proyecto, desc_proyecto,
        cod_actividad, desc_actividad,
        cod_fuente, desc_fuente,
        cod_ubicacion, desc_ubicacion,
        cod_item, desc_item,
        codigo_completo,
        cod_organismo, desc_organismo,
        cod_nprest, desc_nprest
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        trim($row[0]), trim($row[1]), // Programa
        trim($row[2]), trim($row[3]), // Subprograma
        trim($row[4]), trim($row[5]), // Proyecto
        trim($row[6]), trim($row[7]), // Actividad
        trim($row[12]), trim($row[13]), // Fuente
        trim($row[10]), trim($row[11]), // Ubicación
        trim($row[8]), trim($row[9]), // Item
        $codigo_completo,
        trim($row[14]), trim($row[15]), // Organismo
        trim($row[16]), trim($row[17]) // N.Prest
    ]);
}
fclose($handle);
echo "Importación completada.\n";
?>
