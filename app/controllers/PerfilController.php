<?php
/**
 * PerfilController - Controlador para perfil del usuario autenticado
 */

class PerfilController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    /**
     * Ver perfil del usuario
     */
    public function ver() {
        AuthController::verificarAutenticacion();
        
        $usuario_actual = AuthController::obtenerUsuarioActual();
        $usuario = $this->usuario->obtenerPorId($usuario_actual['id']);

        include __DIR__ . '/../views/perfil/ver.php';
    }

    /**
     * Mostrar formulario para cambiar contraseña
     */
    public function cambiarContraseña() {
        AuthController::verificarAutenticacion();
        
        include __DIR__ . '/../views/perfil/cambiar_contraseña.php';
    }

    /**
     * Procesar cambio de contraseña
     */
    public function procesarCambioContraseña() {
        AuthController::verificarAutenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?action=perfil&method=cambiarContraseña');
            exit;
        }

        $usuario_actual = AuthController::obtenerUsuarioActual();
        $usuario_datos = $this->usuario->obtenerPorId($usuario_actual['id']);
        
        $contraseña_actual = $_POST['contraseña_actual'] ?? '';
        $contraseña_nueva = $_POST['contraseña_nueva'] ?? '';
        $confirmar_contraseña = $_POST['confirmar_contraseña'] ?? '';

        // Validaciones
        if (empty($contraseña_actual) || empty($contraseña_nueva) || empty($confirmar_contraseña)) {
            $_SESSION['error'] = 'Todos los campos son requeridos';
            header('Location: ?action=perfil&method=cambiarContraseña');
            exit;
        }

        if (!$this->usuario->verificarContraseña($contraseña_actual, $usuario_datos['contraseña'])) {
            $_SESSION['error'] = 'Contraseña actual incorrecta';
            header('Location: ?action=perfil&method=cambiarContraseña');
            exit;
        }

        if (strlen($contraseña_nueva) < 8) {
            $_SESSION['error'] = 'La nueva contraseña debe tener mínimo 8 caracteres';
            header('Location: ?action=perfil&method=cambiarContraseña');
            exit;
        }

        if ($contraseña_nueva !== $confirmar_contraseña) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header('Location: ?action=perfil&method=cambiarContraseña');
            exit;
        }

        try {
            if ($this->usuario->cambiarContraseña($usuario_actual['id'], $contraseña_nueva)) {
                $_SESSION['success'] = 'Contraseña cambiada exitosamente';
                if (ob_get_level() > 0) ob_end_clean();
                header('Location: ?action=perfil&method=ver');
            } else {
                $_SESSION['error'] = 'Error al cambiar la contraseña';
                if (ob_get_level() > 0) ob_end_clean();
                header('Location: ?action=perfil&method=cambiarContraseña');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            if (ob_get_level() > 0) ob_end_clean();
            header('Location: ?action=perfil&method=cambiarContraseña');
        }
    }
}
