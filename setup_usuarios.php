<?php
/**
 * Script para verificar y crear tabla de usuarios
 * Ejecutar desde: http://localhost/programas/certificados-sistema/setup_usuarios.php
 */

require_once 'app/config.php';
require_once 'app/Database.php';

$db = Database::getInstance()->getConnection();

echo "<h1>Configuración de Usuarios - Sistema de Certificados</h1>";
echo "<hr>";

try {
    // Verificar si la tabla existe
    $query = "SELECT EXISTS (
        SELECT 1 FROM information_schema.tables 
        WHERE table_name = 'usuarios'
    ) as table_exists";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['table_exists']) {
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✓ La tabla 'usuarios' YA EXISTE en la base de datos";
        echo "</div>";
    } else {
        echo "<div style='color: orange; padding: 10px; border: 1px solid orange; margin: 10px 0;'>";
        echo "⚠ La tabla 'usuarios' NO EXISTE. Creando...";
        echo "</div>";
        
        // Crear tabla
        $sql = file_get_contents('database/crear_tabla_usuarios.sql');
        $db->exec($sql);
        
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✓ Tabla 'usuarios' creada exitosamente";
        echo "</div>";
    }

    echo "<hr>";
    echo "<h2>Listado de Usuarios Actual</h2>";
    
    $query = "SELECT id, nombre, apellidos, correo_institucional, tipo_usuario, estado FROM usuarios ORDER BY id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    if (empty($usuarios)) {
        echo "<p style='color: #666;'>No hay usuarios registrados aún.</p>";
        echo "<h3>Creando usuarios de prueba...</h3>";
        
        // Crear usuario admin
        $nombre_admin = "Juan";
        $apellidos_admin = "Pérez Admin";
        $correo_admin = "admin@institucion.com";
        $cargo_admin = "Administrador del Sistema";
        $tipo_admin = "admin";
        $contraseña_admin = password_hash("admin123", PASSWORD_BCRYPT);
        
        $query_admin = "INSERT INTO usuarios (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña) 
                        VALUES (:nombre, :apellidos, :correo, :cargo, :tipo, :pass)";
        $stmt_admin = $db->prepare($query_admin);
        $stmt_admin->execute([
            ':nombre' => $nombre_admin,
            ':apellidos' => $apellidos_admin,
            ':correo' => $correo_admin,
            ':cargo' => $cargo_admin,
            ':tipo' => $tipo_admin,
            ':pass' => $contraseña_admin
        ]);
        
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✓ Usuario ADMIN creado:<br>";
        echo "  Nombre: Juan Pérez Admin<br>";
        echo "  Correo: admin@institucion.com<br>";
        echo "  Contraseña: admin123<br>";
        echo "  Tipo: admin";
        echo "</div>";
        
        // Crear usuario encargado
        $nombre_enc = "María";
        $apellidos_enc = "García Encargada";
        $correo_enc = "encargado@institucion.com";
        $cargo_enc = "Encargado de Certificados";
        $tipo_enc = "encargado";
        $contraseña_enc = password_hash("encargado123", PASSWORD_BCRYPT);
        
        $query_enc = "INSERT INTO usuarios (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña) 
                      VALUES (:nombre, :apellidos, :correo, :cargo, :tipo, :pass)";
        $stmt_enc = $db->prepare($query_enc);
        $stmt_enc->execute([
            ':nombre' => $nombre_enc,
            ':apellidos' => $apellidos_enc,
            ':correo' => $correo_enc,
            ':cargo' => $cargo_enc,
            ':tipo' => $tipo_enc,
            ':pass' => $contraseña_enc
        ]);
        
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✓ Usuario ENCARGADO creado:<br>";
        echo "  Nombre: María García Encargada<br>";
        echo "  Correo: encargado@institucion.com<br>";
        echo "  Contraseña: encargado123<br>";
        echo "  Tipo: encargado";
        echo "</div>";
        
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #333; color: white;'>";
        echo "<th>ID</th><th>Nombre</th><th>Apellidos</th><th>Correo</th><th>Tipo</th><th>Estado</th>";
        echo "</tr>";
        
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>{$usuario['id']}</td>";
            echo "<td>{$usuario['nombre']}</td>";
            echo "<td>{$usuario['apellidos']}</td>";
            echo "<td>{$usuario['correo_institucional']}</td>";
            echo "<td>{$usuario['tipo_usuario']}</td>";
            echo "<td>{$usuario['estado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<div style='padding: 10px; background-color: #f0f0f0; border-radius: 5px;'>";
    echo "<p><strong>✓ Configuración completada</strong></p>";
    echo "<p><a href='index.php' style='color: blue; text-decoration: none;'>← Volver al inicio</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "✗ Error: " . $e->getMessage();
    echo "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
