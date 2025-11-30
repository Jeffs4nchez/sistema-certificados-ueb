<?php
include '../app/Database.php';

$db = Database::getInstance()->getConnection();

// Array de columnas a agregar
$columns = [
    'tipo_documento' => "ALTER TABLE certificados ADD COLUMN tipo_documento VARCHAR(255) NULL AFTER descripcion",
    'clase_documento' => "ALTER TABLE certificados ADD COLUMN clase_documento VARCHAR(255) NULL AFTER tipo_documento",
    'clase_registro' => "ALTER TABLE certificados ADD COLUMN clase_registro VARCHAR(255) NULL AFTER clase_documento",
    'clase_gasto' => "ALTER TABLE certificados ADD COLUMN clase_gasto VARCHAR(255) NULL AFTER clase_registro"
];

// Check y agregar columnas
$checkColumn = "SELECT column_name FROM information_schema.columns WHERE table_name='certificados' AND column_name=?";

foreach ($columns as $colName => $sql) {
    $stmt = $db->prepare($checkColumn);
    $stmt->execute([(string)$colName]);
    $result = $stmt->fetch();
    
    if (!$result) {
        if ($db->query($sql)) {
            echo "✓ Column $colName added successfully\n";
        } else {
            echo "Error adding $colName: " . $db->errorInfo()[2] . "\n";
        }
    } else {
        echo "✓ Column $colName already exists\n";
    }
}

echo "\nAll migrations completed!";
?>
