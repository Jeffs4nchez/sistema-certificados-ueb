<?php
require 'app/Database.php';

$db = Database::getInstance()->getConnection();

$tables = ['ubicaciones', 'fuentes_financiamiento', 'organismos', 'naturaleza_prestacion', 'programas', 'subprogramas', 'proyectos', 'actividades', 'items'];

foreach ($tables as $table) {
    echo "\n=== Tabla: $table ===\n";
    $result = $db->query("SELECT column_name, data_type, character_maximum_length FROM information_schema.columns WHERE table_name = '$table' ORDER BY ordinal_position;");
    
    if ($result) {
        while ($row = $result->fetch()) {
            $maxlen = $row['character_maximum_length'] ?? '-';
            echo $row['column_name'] . ': ' . $row['data_type'] . '(' . $maxlen . ')' . "\n";
        }
    }
}
?>
