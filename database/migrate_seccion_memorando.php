<?php
include '../app/Database.php';

$db = Database::getInstance()->getConnection();

// Check if column already exists
$checkColumn = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='certificados' AND COLUMN_NAME='seccion_memorando'";

$result = $db->query($checkColumn);

if ($result->num_rows > 0) {
    echo "✓ Column seccion_memorando already exists";
} else {
    $sql = "ALTER TABLE certificados ADD COLUMN seccion_memorando VARCHAR(255) NULL AFTER institucion";
    
    if ($db->query($sql)) {
        echo "✓ Column seccion_memorando added successfully";
    } else {
        echo "Error: " . $db->error;
    }
}
?>
