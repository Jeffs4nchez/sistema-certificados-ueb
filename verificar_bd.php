<?php
/**
 * Script para verificar la estructura de la base de datos
 */

require_once 'app/config.php';
require_once 'app/Database.php';

echo "<h1>Verificación de Base de Datos</h1>";
echo "<hr>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Verificar conexión
    echo "<h2>1. Conexión a PostgreSQL</h2>";
    $result = $db->query("SELECT version()");
    $version = $result->fetch();
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "✓ Conectado a: " . substr($version[0], 0, 50) . "...";
    echo "</div>";
    
    // Verificar base de datos
    echo "<h2>2. Base de Datos Actual</h2>";
    $result = $db->query("SELECT current_database()");
    $dbname = $result->fetch();
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "✓ Base de datos: <strong>" . $dbname[0] . "</strong>";
    echo "</div>";
    
    // Listar todas las tablas
    echo "<h2>3. Tablas en la Base de Datos</h2>";
    $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";
    $result = $db->query($query);
    $tables = $result->fetchAll();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #333; color: white;'>";
    echo "<th>Nombre de Tabla</th><th>Estado</th>";
    echo "</tr>";
    
    $tabla_usuarios_existe = false;
    foreach ($tables as $table) {
        $tabla = $table['table_name'];
        $existe = $tabla === 'usuarios' ? '✓ EXISTE' : 'OK';
        $color = $tabla === 'usuarios' ? '#d4edda' : '#f0f0f0';
        
        if ($tabla === 'usuarios') {
            $tabla_usuarios_existe = true;
        }
        
        echo "<tr style='background-color: $color;'>";
        echo "<td><strong>$tabla</strong></td>";
        echo "<td>$existe</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    
    // Si tabla usuarios existe, verificar su estructura
    if ($tabla_usuarios_existe) {
        echo "<h2>4. Estructura de la Tabla 'usuarios'</h2>";
        
        $query = "SELECT column_name, data_type, is_nullable, column_default 
                  FROM information_schema.columns 
                  WHERE table_name = 'usuarios' 
                  ORDER BY ordinal_position";
        $result = $db->query($query);
        $columns = $result->fetchAll();
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #333; color: white;'>";
        echo "<th>Columna</th><th>Tipo</th><th>Nullable</th><th>Default</th>";
        echo "</tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><strong>" . $col['column_name'] . "</strong></td>";
            echo "<td>" . $col['data_type'] . "</td>";
            echo "<td>" . ($col['is_nullable'] === 'YES' ? 'Sí' : 'No') . "</td>";
            echo "<td>" . ($col['column_default'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<hr>";
        echo "<h2>5. Datos en la Tabla 'usuarios'</h2>";
        
        $query = "SELECT id, nombre, apellidos, correo_institucional, tipo_usuario, estado FROM usuarios ORDER BY id";
        $result = $db->query($query);
        $usuarios = $result->fetchAll();
        
        if (empty($usuarios)) {
            echo "<div style='color: orange; padding: 10px; border: 1px solid orange; margin: 10px 0;'>";
            echo "⚠ La tabla existe pero NO HAY USUARIOS";
            echo "</div>";
        } else {
            echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #333; color: white;'>";
            echo "<th>ID</th><th>Nombre</th><th>Apellidos</th><th>Correo</th><th>Tipo</th><th>Estado</th>";
            echo "</tr>";
            
            foreach ($usuarios as $user) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td>" . htmlspecialchars($user['nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($user['apellidos']) . "</td>";
                echo "<td>" . htmlspecialchars($user['correo_institucional']) . "</td>";
                echo "<td><strong>" . $user['tipo_usuario'] . "</strong></td>";
                echo "<td>" . $user['estado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<hr>";
            echo "<div style='color: green; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "<h3>✓ ¡Todo está listo!</h3>";
            echo "<p>Total de usuarios: <strong>" . count($usuarios) . "</strong></p>";
            echo "<p><a href='index.php' style='color: blue; text-decoration: none; font-weight: bold;'>→ Ir al login</a></p>";
            echo "</div>";
        }
        
        // Verificar relación con certificados
        echo "<hr>";
        echo "<h2>6. Relación con Tabla 'certificados'</h2>";
        
        $query = "SELECT EXISTS(SELECT 1 FROM information_schema.columns 
                  WHERE table_name = 'certificados' AND column_name = 'usuario_id') as existe";
        $result = $db->query($query);
        $rel = $result->fetch();
        
        if ($rel['existe']) {
            echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
            echo "✓ Columna 'usuario_id' existe en certificados";
            echo "</div>";
        } else {
            echo "<div style='color: orange; padding: 10px; border: 1px solid orange; margin: 10px 0;'>";
            echo "⚠ Columna 'usuario_id' NO existe en certificados - ejecutar: <code>crear_tabla_directa.php</code>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='color: red; padding: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h3>✗ LA TABLA 'usuarios' NO EXISTE</h3>";
        echo "<p>Necesitas ejecutar el setup. <a href='crear_tabla_directa.php' style='color: red; font-weight: bold;'>→ Ejecutar Setup</a></p>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div style='color: red; padding: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; border-radius: 5px;'>";
    echo "<h3>✗ Error en la Conexión</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}
?>
