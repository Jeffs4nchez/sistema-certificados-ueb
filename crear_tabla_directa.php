<?php
/**
 * Script directo para crear la tabla usuarios en PostgreSQL
 * Ejecutar desde: http://localhost/programas/certificados-sistema/crear_tabla_directa.php
 */

require_once 'app/config.php';
require_once 'app/Database.php';

echo "<h1>Creando tabla usuarios...</h1>";
echo "<hr>";

try {
    $db = Database::getInstance()->getConnection();
    
    // SQL para crear la tabla usuarios
    $sql_usuarios = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        correo_institucional VARCHAR(255) NOT NULL UNIQUE,
        cargo VARCHAR(100) NOT NULL,
        tipo_usuario VARCHAR(50) NOT NULL,
        contraseña VARCHAR(255) NOT NULL,
        estado VARCHAR(20) DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    // Crear índices
    $sql_indices = "
    CREATE INDEX IF NOT EXISTS idx_usuarios_correo ON usuarios(correo_institucional);
    CREATE INDEX IF NOT EXISTS idx_usuarios_estado ON usuarios(estado);
    ";
    
    // Agregar columna usuario_id a certificados si no existe
    $sql_alter = "
    DO \$\$ 
    BEGIN
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                       WHERE table_name = 'certificados' AND column_name = 'usuario_id') THEN
            ALTER TABLE certificados 
            ADD COLUMN usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL;
            
            CREATE INDEX IF NOT EXISTS idx_certificados_usuario_id ON certificados(usuario_id);
        END IF;
    END \$\$;
    ";
    
    // Ejecutar creación de tabla
    $db->exec($sql_usuarios);
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "✓ Tabla 'usuarios' creada/verificada exitosamente";
    echo "</div>";
    
    // Ejecutar índices
    $db->exec($sql_indices);
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "✓ Índices creados exitosamente";
    echo "</div>";
    
    // Ejecutar alter table
    $db->exec($sql_alter);
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "✓ Relación con certificados establecida";
    echo "</div>";
    
    echo "<hr>";
    echo "<h2>Verificando usuarios existentes...</h2>";
    
    $query = "SELECT COUNT(*) as total FROM usuarios";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    $total_usuarios = $result['total'] ?? 0;
    
    if ($total_usuarios == 0) {
        echo "<p>No hay usuarios. Creando usuarios de prueba...</p>";
        
        // Usuario ADMIN
        $contraseña_admin = password_hash('admin123', PASSWORD_BCRYPT);
        
        $stmt_admin = $db->prepare("INSERT INTO usuarios 
            (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña) 
            VALUES (:nombre, :apellidos, :correo, :cargo, :tipo, :pass)");
        
        $stmt_admin->execute([
            ':nombre' => 'Juan',
            ':apellidos' => 'Pérez Admin',
            ':correo' => 'admin@institucion.com',
            ':cargo' => 'Administrador del Sistema',
            ':tipo' => 'admin',
            ':pass' => $contraseña_admin
        ]);
        
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✓ Usuario ADMIN creado:<br>";
        echo "  Correo: admin@institucion.com<br>";
        echo "  Contraseña: admin123<br>";
        echo "  Tipo: admin";
        echo "</div>";
        
        // Usuario ENCARGADO
        $contraseña_enc = password_hash('encargado123', PASSWORD_BCRYPT);
        
        $stmt_enc = $db->prepare("INSERT INTO usuarios 
            (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña) 
            VALUES (:nombre, :apellidos, :correo, :cargo, :tipo, :pass)");
        
        $stmt_enc->execute([
            ':nombre' => 'María',
            ':apellidos' => 'García Encargada',
            ':correo' => 'encargado@institucion.com',
            ':cargo' => 'Encargado de Certificados',
            ':tipo' => 'encargado',
            ':pass' => $contraseña_enc
        ]);
        
        echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✓ Usuario ENCARGADO creado:<br>";
        echo "  Correo: encargado@institucion.com<br>";
        echo "  Contraseña: encargado123<br>";
        echo "  Tipo: encargado";
        echo "</div>";
        
        $total_usuarios = 2;
    }
    
    echo "<hr>";
    echo "<h2>Listado de Usuarios</h2>";
    
    $query = "SELECT id, nombre, apellidos, correo_institucional, tipo_usuario, estado FROM usuarios ORDER BY id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
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
        echo "<td><strong>{$usuario['tipo_usuario']}</strong></td>";
        echo "<td>{$usuario['estado']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<div style='padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;'>";
    echo "<h3>✓ ¡Setup completado exitosamente!</h3>";
    echo "<p>Total de usuarios: <strong>" . $total_usuarios . "</strong></p>";
    echo "<p><a href='index.php' style='color: blue; text-decoration: none; font-weight: bold;'>→ Ir al login</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "✗ Error: " . $e->getMessage();
    echo "</div>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
