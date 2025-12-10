<?php
/**
 * Script para crear administrador por defecto
 * Se ejecuta solo si no existe ningún administrador en el sistema
 * PROTECCIÓN: El administrador por defecto no puede ser eliminado si es el único
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'certificados_sistema');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

if (!$mysqli->select_db(DB_NAME)) {
    die("Base de datos no existe. Ejecuta install.php primero.\n");
}

echo "[INFO] Verificando administrador por defecto...\n";

// Verificar si existe un administrador activo
$query = "SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'admin' AND estado = 'activo'";
$result = $mysqli->query($query);
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    echo "[OK] Ya existe un administrador en el sistema.\n";
    $mysqli->close();
    exit(0);
}

echo "[INFO] No existe administrador. Creando usuario por defecto...\n";

// Datos del administrador por defecto
$nombre = "Administrador";
$apellidos = "Sistema";
$correo = "admin@sistema.local";
$cargo = "Administrador del Sistema";
$tipo_usuario = "admin";
$contraseña = "Admin123!"; // Contraseña por defecto
$contraseña_hash = password_hash($contraseña, PASSWORD_BCRYPT);
$estado = "activo";

// Insertar administrador
$stmt = $mysqli->prepare("
    INSERT INTO usuarios 
    (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña, estado, fecha_creacion)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    die("Error en la consulta: " . $mysqli->error . "\n");
}

$stmt->bind_param(
    "sssssss",
    $nombre,
    $apellidos,
    $correo,
    $cargo,
    $tipo_usuario,
    $contraseña_hash,
    $estado
);

if ($stmt->execute()) {
    echo "\n[✓] ¡Administrador creado exitosamente!\n";
    echo "[INFO] Credenciales de acceso:\n";
    echo "       Correo: {$correo}\n";
    echo "       Contraseña: {$contraseña}\n";
    echo "\n[⚠️] IMPORTANTE: Cambia la contraseña en el perfil después de primer acceso.\n";
    echo "[🔒] PROTECCIÓN: Este administrador no puede ser eliminado si es el único activo.\n";
} else {
    echo "[ERROR] Error al crear administrador: " . $stmt->error . "\n";
}

$stmt->close();
$mysqli->close();
?>
