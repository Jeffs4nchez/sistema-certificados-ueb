<?php
require_once 'app/config.php';
require_once 'app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Verificar tablas
    $result = $db->query('SELECT table_name FROM information_schema.tables WHERE table_schema = \'public\' ORDER BY table_name');
    $tables = $result->fetchAll();
    
    echo "TABLAS EN LA BASE DE DATOS:\n";
    echo "============================\n";
    foreach($tables as $t) {
        echo "  - " . $t['table_name'] . "\n";
    }
    
    // Verificar si existe tabla usuarios
    echo "\n\nVERIFICANDO TABLA 'usuarios':\n";
    echo "=============================\n";
    
    $query = 'SELECT EXISTS(SELECT 1 FROM information_schema.tables WHERE table_name = \'usuarios\')';
    $result = $db->query($query);
    $exists = $result->fetch();
    
    if ($exists[0] == 't') {
        echo "✓ LA TABLA 'usuarios' EXISTE\n";
        
        // Contar usuarios
        $result = $db->query('SELECT COUNT(*) as total FROM usuarios');
        $count = $result->fetch();
        echo "  Total de usuarios: " . $count['total'] . "\n";
        
        if ($count['total'] > 0) {
            echo "\nDATOS EN LA TABLA:\n";
            $result = $db->query('SELECT id, nombre, correo_institucional, tipo_usuario FROM usuarios');
            $users = $result->fetchAll();
            foreach($users as $user) {
                echo "  ID: " . $user['id'] . " | " . $user['nombre'] . " | " . $user['correo_institucional'] . " | " . $user['tipo_usuario'] . "\n";
            }
        } else {
            echo "  ⚠ NO HAY USUARIOS - Ejecutar: crear_tabla_directa.php\n";
        }
    } else {
        echo "✗ LA TABLA 'usuarios' NO EXISTE\n";
        echo "  Necesitas ejecutar: crear_tabla_directa.php\n";
    }
    
} catch (Exception $e) {
    echo "ERROR EN LA CONEXIÓN:\n";
    echo $e->getMessage() . "\n";
}
?>
