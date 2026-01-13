<?php
/**
 * UsuarioController - Controlador para gestionar usuarios
 */

class UsuarioController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    /**
     * Mostrar lista de usuarios
     */
    public function listar() {
        // Solo admin puede gestionar usuarios
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden gestionar usuarios.');
        }

        try {
            // Recopilar filtros del GET
            $filtros = [];
            if (!empty($_GET['buscar'])) {
                $filtros['buscar'] = $_GET['buscar'];
            }
            if (!empty($_GET['cargo'])) {
                $filtros['cargo'] = $_GET['cargo'];
            }
            if (!empty($_GET['tipo'])) {
                $filtros['tipo'] = $_GET['tipo'];
            }
            if (!empty($_GET['estado'])) {
                $filtros['estado'] = $_GET['estado'];
            }

            // Obtener usuarios con filtros si existen, sino todos
            if (!empty($filtros)) {
                $usuarios = $this->usuario->obtenerConFiltros($filtros);
            } else {
                $usuarios = $this->usuario->obtenerTodos();
            }

            include __DIR__ . '/../views/usuarios/list.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al obtener usuarios: " . $e->getMessage();
            header('Location: ?action=dashboard');
        }
    }

    /**
     * Mostrar formulario para crear usuario
     */
    public function crearFormulario() {
        // Solo admin puede crear usuarios
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden crear usuarios.');
        }

        $tipos_usuario = ['admin', 'operador', 'consultor'];
        include __DIR__ . '/../views/usuarios/form.php';
    }

    /**
     * Guardar nuevo usuario
     */
    public function guardar() {
        // Solo admin puede crear usuarios
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden crear usuarios.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar datos
                if (empty($_POST['nombre']) || empty($_POST['apellidos']) || 
                    empty($_POST['correo_institucional']) || empty($_POST['cargo']) || 
                    empty($_POST['tipo_usuario']) || empty($_POST['contraseña'])) {
                    $_SESSION['error'] = 'Todos los campos son requeridos';
                    header('Location: ?action=usuario&method=crearFormulario');
                    return;
                }

                // Verificar que el correo sea único
                $usuario_existente = $this->usuario->obtenerPorCorreo($_POST['correo_institucional']);
                if ($usuario_existente) {
                    $_SESSION['error'] = 'El correo institucional ya está registrado';
                    header('Location: ?action=usuario&method=crearFormulario');
                    return;
                }

                // Asignar valores
                $this->usuario->nombre = $_POST['nombre'];
                $this->usuario->apellidos = $_POST['apellidos'];
                $this->usuario->correo_institucional = $_POST['correo_institucional'];
                $this->usuario->cargo = $_POST['cargo'];
                $this->usuario->tipo_usuario = $_POST['tipo_usuario'];
                $this->usuario->contraseña = $_POST['contraseña'];

                // Crear usuario
                if ($this->usuario->crear()) {
                    $_SESSION['success'] = 'Usuario creado exitosamente';
                    if (ob_get_level() > 0) ob_end_clean();
                    header('Location: ?action=usuario&method=listar');
                } else {
                    $_SESSION['error'] = 'Error al crear el usuario';
                    if (ob_get_level() > 0) ob_end_clean();
                    header('Location: ?action=usuario&method=crearFormulario');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                if (ob_get_level() > 0) ob_end_clean();
                header('Location: ?action=usuario&method=crearFormulario');
            }
        }
    }

    /**
     * Mostrar formulario para editar usuario
     */
    public function editarFormulario() {
        // Solo admin puede editar usuarios
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden editar usuarios.');
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no especificado';
            header('Location: ?action=usuario&method=listar');
            return;
        }

        $usuario = $this->usuario->obtenerPorId($id);
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: ?action=usuario&method=listar');
            return;
        }

        $tipos_usuario = ['admin', 'operador', 'consultor'];
        $editar = true;
        include __DIR__ . '/../views/usuarios/form.php';
    }

    /**
     * Actualizar usuario
     */
    public function actualizar() {
        // Solo admin puede editar usuarios
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden editar usuarios.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    $_SESSION['error'] = 'ID de usuario no especificado';
                    header('Location: ?action=usuario&method=listar');
                    return;
                }

                // Validar datos
                if (empty($_POST['nombre']) || empty($_POST['apellidos']) || 
                    empty($_POST['cargo']) || empty($_POST['tipo_usuario'])) {
                    $_SESSION['error'] = 'Todos los campos son requeridos';
                    header('Location: ?action=usuario&method=editarFormulario&id=' . $id);
                    return;
                }

                $this->usuario->id = $id;
                $this->usuario->nombre = $_POST['nombre'];
                $this->usuario->apellidos = $_POST['apellidos'];
                $this->usuario->cargo = $_POST['cargo'];
                $this->usuario->tipo_usuario = $_POST['tipo_usuario'];
                $this->usuario->estado = $_POST['estado'] ?? 'activo';

                if ($this->usuario->actualizar()) {
                    $_SESSION['success'] = 'Usuario actualizado exitosamente';
                    if (ob_get_level() > 0) ob_end_clean();
                    header('Location: ?action=usuario&method=listar');
                } else {
                    $_SESSION['error'] = 'Error al actualizar el usuario';
                    if (ob_get_level() > 0) ob_end_clean();
                    header('Location: ?action=usuario&method=editarFormulario&id=' . $id);
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                if (ob_get_level() > 0) ob_end_clean();
                header('Location: ?action=usuario&method=listar');
            }
        }
    }

    /**
     * Eliminar usuario
     * PROTECCIÓN: No permite eliminar el último administrador ni el usuario root
     */
    public function eliminar() {
        // Solo admin puede eliminar usuarios
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden eliminar usuarios.');
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no especificado';
            header('Location: ?action=usuario&method=listar');
            return;
        }

        try {
            // Obtener el usuario a eliminar
            $usuario_a_eliminar = $this->usuario->obtenerPorId($id);
            if (!$usuario_a_eliminar) {
                $_SESSION['error'] = 'Usuario no encontrado';
                header('Location: ?action=usuario&method=listar');
                return;
            }

            // Protección: No permitir eliminar el usuario root (primer admin)
            if (isset($usuario_a_eliminar['es_root']) && $usuario_a_eliminar['es_root'] === 1) {
                $_SESSION['error'] = 'No se puede eliminar el usuario administrador principal del sistema';
                header('Location: ?action=usuario&method=listar');
                return;
            }

            // Intentar eliminar
            if ($this->usuario->eliminar($id)) {
                $_SESSION['success'] = 'Usuario desactivado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al desactivar el usuario';
            }
            if (ob_get_level() > 0) ob_end_clean();
            header('Location: ?action=usuario&method=listar');
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            if (ob_get_level() > 0) ob_end_clean();
            header('Location: ?action=usuario&method=listar');
        }
    }

    /**
     * Ver detalles del usuario
     */
    public function ver() {
        // Solo admin puede ver otros usuarios
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden ver usuarios.');
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no especificado';
            header('Location: ?action=usuario&method=listar');
            return;
        }

        $usuario = $this->usuario->obtenerPorId($id);
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: ?action=usuario&method=listar');
            return;
        }

        $certificados = $this->usuario->obtenerCertificados($id);
        include __DIR__ . '/../views/usuarios/view.php';
    }

    /**
     * Resetear contraseña de un usuario
     * Genera una contraseña temporal que el usuario debe cambiar al primer login
     */
    public function resetearContraseña() {
        // Solo admin puede resetear contraseñas
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden resetear contraseñas.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    $_SESSION['error'] = 'ID de usuario no especificado';
                    header('Location: ?action=usuario&method=listar');
                    return;
                }

                // Obtener usuario
                $usuario = $this->usuario->obtenerPorId($id);
                if (!$usuario) {
                    $_SESSION['error'] = 'Usuario no encontrado';
                    header('Location: ?action=usuario&method=listar');
                    return;
                }

                // Generar contraseña temporal de 8 caracteres
                $contraseña_temporal = $this->generarContraseñaTemporal();

                // Actualizar contraseña en la BD
                if ($this->usuario->cambiarContraseña($id, $contraseña_temporal)) {
                    $_SESSION['success'] = "Contraseña reseteada exitosamente. Nueva contraseña temporal: <strong>{$contraseña_temporal}</strong>. Comparte esta contraseña de manera segura con el usuario.";
                    if (ob_get_level() > 0) ob_end_clean();
                    header('Location: ?action=usuario&method=listar');
                } else {
                    $_SESSION['error'] = 'Error al resetear la contraseña';
                    if (ob_get_level() > 0) ob_end_clean();
                    header('Location: ?action=usuario&method=listar');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                if (ob_get_level() > 0) ob_end_clean();
                header('Location: ?action=usuario&method=listar');
            }
        }
    }

    /**
     * Generar contraseña temporal aleatoria
     * @return string Contraseña temporal de 8 caracteres
     */
    private function generarContraseñaTemporal() {
        // Caracteres permitidos
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
        $contraseña = '';
        
        // Generar 8 caracteres aleatorios
        for ($i = 0; $i < 8; $i++) {
            $contraseña .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        
        return $contraseña;
    }
}
