<?php
/**
 * AuthController - Controlador de autenticación
 */

class AuthController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    /**
     * Mostrar formulario de login
     */
    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ?action=dashboard');
            exit;
        }
        
        include __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesar login
     */
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?action=auth&method=login');
            exit;
        }

        $correo = $_POST['correo'] ?? '';
        $contraseña = $_POST['contraseña'] ?? '';

        if (empty($correo) || empty($contraseña)) {
            $_SESSION['error'] = 'Correo y contraseña son requeridos';
            header('Location: ?action=auth&method=login');
            exit;
        }

        // Autenticar usuario
        $usuario = $this->usuario->autenticar($correo, $contraseña);

        if ($usuario && $usuario['estado'] === 'activo') {
            // Crear sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellidos'];
            $_SESSION['usuario_correo'] = $usuario['correo_institucional'];
            $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'];
            $_SESSION['usuario_cargo'] = $usuario['cargo'];

            // Redirigir a dashboard
            header('Location: ?action=dashboard');
            exit;
        } else {
            $_SESSION['error'] = 'Correo o contraseña incorrectos, o usuario inactivo';
            header('Location: ?action=auth&method=login');
            exit;
        }
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        header('Location: ?action=auth&method=login');
        exit;
    }

    /**
     * Verificar si usuario está autenticado
     */
    public static function verificarAutenticacion() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ?action=auth&method=login');
            exit;
        }
    }

    /**
     * Obtener datos del usuario actual
     */
    public static function obtenerUsuarioActual() {
        return [
            'id' => $_SESSION['usuario_id'] ?? null,
            'nombre' => $_SESSION['usuario_nombre'] ?? '',
            'correo' => $_SESSION['usuario_correo'] ?? '',
            'tipo' => $_SESSION['usuario_tipo'] ?? '',
            'cargo' => $_SESSION['usuario_cargo'] ?? ''
        ];
    }

    /**
     * Verificar permiso
     */
    public static function verificarPermiso($tipo_requerido) {
        if ($_SESSION['usuario_tipo'] !== $tipo_requerido) {
            $_SESSION['error'] = 'No tienes permisos para acceder a esta función';
            header('Location: ?action=dashboard');
            exit;
        }
    }
}
