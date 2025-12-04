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
