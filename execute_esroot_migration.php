<?php
/**
 * Script para ejecutar migraciones de es_root
 * Accede a: http://localhost/programas/certificados-sistema/execute_esroot_migration.php
 */

require_once __DIR__ . '/app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>Ejecutando Migración es_root...</h2>";
    echo "<pre>";
    
    // 1. Verificar si la tabla usuarios existe
    echo "1. Verificando si la tabla 'usuarios' existe...\n";
    $query = "SELECT EXISTS (
        SELECT 1 FROM information_schema.tables 
        WHERE table_schema = 'public' AND table_name = 'usuarios'
    ) as table_exists";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if (!$result['table_exists']) {
        echo "   ✓ La tabla 'usuarios' NO existe. Creándola...\n";
        $sqlContent = file_get_contents(__DIR__ . '/database/crear_tabla_usuarios.sql');
        $db->exec($sqlContent);
        echo "   ✓ Tabla 'usuarios' creada correctamente\n";
    } else {
        echo "   ✓ La tabla 'usuarios' YA existe\n";
    }
    
    // 2. Verificar si la columna es_root existe
    echo "\n2. Verificando si la columna 'es_root' existe...\n";
    $query = "SELECT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = 'public' AND table_name = 'usuarios' AND column_name = 'es_root'
    ) as column_exists";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if (!$result['column_exists']) {
        echo "   ✓ La columna 'es_root' NO existe. Agregándola...\n";
        $db->exec("ALTER TABLE usuarios ADD COLUMN es_root INTEGER DEFAULT 0");
        echo "   ✓ Columna 'es_root' agregada\n";
        
        // Marcar el primer usuario como root
        echo "\n3. Marcando el primer usuario como root...\n";
        $db->exec("UPDATE usuarios SET es_root = 1 WHERE id = 1");
        echo "   ✓ Primer usuario marcado como root\n";
    } else {
        echo "   ✓ La columna 'es_root' YA existe\n";
    }
    
    echo "\n</pre>";
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "✓ Migración completada exitosamente<br>";
    echo "<a href='index.php'>Volver al inicio</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "</pre>";
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "✗ Error: " . $e->getMessage() . "<br>";
    echo "<a href='index.php'>Volver al inicio</a>";
    echo "</div>";
}
?>
