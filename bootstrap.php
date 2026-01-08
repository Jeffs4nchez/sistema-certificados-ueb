<?php
/**
 * Bootstrap - Configuración Global de la Aplicación
 * Este archivo se carga primero para hacer disponible la clase Database en todo el sistema
 */

// Definir rutas de la aplicación
define('APP_ROOT', __DIR__);
define('APP_PATH', __DIR__ . '/app');
define('DATABASE_CLASS', APP_PATH . '/Database.php');

// Cargar la clase Database una única vez
if (!class_exists('Database')) {
    require_once DATABASE_CLASS;
}

// Crear usuario admin automáticamente si no hay usuarios (primera instalación)
$adminMarkerFile = __DIR__ . '/.admin-created';
if (!file_exists($adminMarkerFile)) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        
        if ($result['total'] == 0) {
            // Crear usuario admin automáticamente
            $stmt = $db->prepare("INSERT INTO usuarios (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña, es_root) 
                                VALUES (:nombre, :apellidos, :correo, :cargo, :tipo, :pass, 1)");
            $stmt->execute([
                ':nombre' => 'Admin',
                ':apellidos' => 'Sistema',
                ':correo' => 'admin@institucion.com',
                ':cargo' => 'Administrador del Sistema',
                ':tipo' => 'admin',
                ':pass' => password_hash('admin123', PASSWORD_BCRYPT)
            ]);
            
            touch($adminMarkerFile); // Marcar que ya se creó
        }
    } catch (Exception $e) {
        // Si hay error, continuar sin fallar
        error_log("Error al crear usuario admin: " . $e->getMessage());
    }
}

// Ejecutar migraciones automáticas
$migrationsFolder = __DIR__ . '/database/migrations';
if (!is_dir($migrationsFolder)) {
    @mkdir($migrationsFolder, 0755, true);
}

// Ejecutar migración para agregar columna es_root
$esRootMigrationMarker = __DIR__ . '/.migration-es-root';
if (!file_exists($esRootMigrationMarker)) {
    $migrationFile = __DIR__ . '/database/migration_es_root.php';
    if (file_exists($migrationFile)) {
        try {
            ob_start();
            require_once $migrationFile;
            ob_end_clean();
            touch($esRootMigrationMarker);
        } catch (Exception $e) {
            error_log("Error en migración es_root: " . $e->getMessage());
        }
    }
}

// Limpiar y reinstalar triggers si no están instalados
$triggerMarkerFile = __DIR__ . '/.triggers-installed';
if (!file_exists($triggerMarkerFile)) {
    $cleanupFile = __DIR__ . '/database/ejecutar_limpieza.php';
    if (file_exists($cleanupFile)) {
        // Capturar salida para no mostrar en pantalla
        ob_start();
        require_once $cleanupFile;
        ob_end_clean();
        touch($triggerMarkerFile); // Marcar que ya se instalaron
    }
}

// Cargar modelos
if (!class_exists('Usuario')) {
    require_once APP_PATH . '/models/Usuario.php';
}

if (!class_exists('Certificate')) {
    require_once APP_PATH . '/models/Certificate.php';
}

if (!class_exists('CertificateItem')) {
    require_once APP_PATH . '/models/CertificateItem.php';
}

// Cargar helpers
if (!class_exists('PermisosHelper')) {
    require_once APP_PATH . '/helpers/PermisosHelper.php';
}

if (!class_exists('Parameter')) {
    require_once APP_PATH . '/models/Parameter.php';
}

if (!class_exists('PresupuestoItem')) {
    require_once APP_PATH . '/models/PresupuestoItem.php';
}

// Cargar controladores base
if (!class_exists('AuthController')) {
    require_once APP_PATH . '/controllers/AuthController.php';
}

?>
