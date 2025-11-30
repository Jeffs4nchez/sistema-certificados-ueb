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

?>
