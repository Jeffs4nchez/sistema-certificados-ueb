<?php
/**
 * Migración: Agregar columna es_root a la tabla usuarios
 * Ejecutar una sola vez para proteger el primer admin
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Verificar si la columna ya existe
    $query = "SELECT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'usuarios' AND column_name = 'es_root'
    ) as column_exists";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if (!$result['column_exists']) {
        // Agregar la columna
        $db->exec("ALTER TABLE usuarios ADD COLUMN es_root INTEGER DEFAULT 0");
        
        // El primer usuario (ID 1) es el root
        $db->exec("UPDATE usuarios SET es_root = 1 WHERE id = 1");
        
        echo "✓ Columna 'es_root' agregada a la tabla usuarios<br>";
        echo "✓ Usuario ID 1 marcado como root (protegido)<br>";
    } else {
        echo "✓ La columna 'es_root' ya existe<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
