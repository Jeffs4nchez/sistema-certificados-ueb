<?php
require_once 'app/config.php';
require_once 'app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Verificando columna usuario_creacion...\n";
    
    // Usar try-catch para cada ALTER TABLE
    try {
        $db->exec("ALTER TABLE certificados ADD COLUMN usuario_creacion VARCHAR(255)");
        echo "✓ Columna usuario_creacion agregada\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'ya existe') !== false) {
            echo "✓ Columna usuario_creacion ya existe\n";
        } else {
            throw $e;
        }
    }
    
    echo "\nEstructura actual de certificados:\n";
    $query = "SELECT column_name, data_type FROM information_schema.columns 
              WHERE table_name = 'certificados' ORDER BY ordinal_position";
    $result = $db->query($query);
    $columns = $result->fetchAll();
    
    foreach ($columns as $col) {
        echo "  - " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
    }
    
    echo "\n✓ ¡Listo! Los certificados ahora registran al usuario que los crea.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
