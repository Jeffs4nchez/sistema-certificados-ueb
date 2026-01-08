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
     * Mostrar formulario de login o pantalla de instalación
     */
    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ?action=dashboard');
            exit;
        }
        
        // Verificar si hay usuarios registrados
        $usuariosExistentes = $this->usuario->obtenerTodos();
        
        // Si no hay usuarios, mostrar pantalla de instalación
        if (empty($usuariosExistentes)) {
            include __DIR__ . '/../views/auth/install.php';
            exit;
        }
        
        // Si hay usuarios, mostrar login normal
        include __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesar instalación inicial (crear primer admin)
     */
    public function instalar() {
        // Verificar si ya hay usuarios
        $usuariosExistentes = $this->usuario->obtenerTodos();
        if (!empty($usuariosExistentes)) {
            header('Location: ?action=auth&method=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?action=auth&method=login');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $contraseña = $_POST['contraseña'] ?? '';
        $contraseña_confirmacion = $_POST['contraseña_confirmacion'] ?? '';

        // Validaciones
        if (empty($nombre) || empty($apellidos) || empty($correo) || empty($contraseña)) {
            $_SESSION['error'] = 'Todos los campos son requeridos';
            header('Location: ?action=auth&method=login');
            exit;
        }

        if (strlen($contraseña) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            header('Location: ?action=auth&method=login');
            exit;
        }

        if ($contraseña !== $contraseña_confirmacion) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header('Location: ?action=auth&method=login');
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo no es válido';
            header('Location: ?action=auth&method=login');
            exit;
        }

        // Crear usuario admin
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO usuarios (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña, es_root) 
                VALUES (:nombre, :apellidos, :correo, :cargo, :tipo, :pass, 1)
            ");
            
            $stmt->execute([
                ':nombre' => $nombre,
                ':apellidos' => $apellidos,
                ':correo' => $correo,
                ':cargo' => $cargo,
                ':tipo' => 'admin',
                ':pass' => password_hash($contraseña, PASSWORD_BCRYPT)
            ]);

            $_SESSION['success'] = 'Administrador creado exitosamente. Inicia sesión con tus credenciales.';
            header('Location: ?action=auth&method=login');
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear el administrador: ' . $e->getMessage();
            header('Location: ?action=auth&method=login');
            exit;
        }
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
        $año_trabajo = $_POST['año_trabajo'] ?? '';

        if (empty($correo) || empty($contraseña)) {
            $_SESSION['error'] = 'Correo y contraseña son requeridos';
            header('Location: ?action=auth&method=login');
            exit;
        }

        if (empty($año_trabajo)) {
            $_SESSION['error'] = 'Debe seleccionar un año de trabajo';
            header('Location: ?action=auth&method=login');
            exit;
        }

        // Validar que el año sea un número válido
        if (!preg_match('/^\d{4}$/', $año_trabajo)) {
            $_SESSION['error'] = 'Año de trabajo inválido';
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
            $_SESSION['year'] = intval($año_trabajo); // GUARDAR YEAR EN SESIÓN

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
            'cargo' => $_SESSION['usuario_cargo'] ?? '',
            'year' => $_SESSION['year'] ?? date('Y')
        ];
    }

    /**
     * Obtener año de trabajo actual
     */
    public static function obtenerAnoTrabajo() {
        return $_SESSION['year'] ?? date('Y');
    }

    /**
     * Cambiar año de trabajo
     */
    public function cambiarAno() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?action=dashboard');
            exit;
        }

        $año_trabajo = $_POST['año_trabajo'] ?? '';

        if (!empty($año_trabajo) && preg_match('/^\d{4}$/', $año_trabajo)) {
            $_SESSION['year'] = intval($año_trabajo);
        }

        // Redirigir a la página anterior
        $referer = $_SERVER['HTTP_REFERER'] ?? '?action=dashboard';
        header('Location: ' . $referer);
        exit;
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
