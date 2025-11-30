<?php
/**
 * Modelo Usuario - Gestiona los usuarios del sistema
 */

class Usuario {
    private $db;
    private $table = 'usuarios';

    // Propiedades del usuario
    public $id;
    public $nombre;
    public $apellidos;
    public $correo_institucional;
    public $cargo;
    public $tipo_usuario;
    public $contraseña;
    public $estado;
    public $fecha_creacion;
    public $fecha_actualizacion;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos() {
        $query = "SELECT * FROM {$this->table} WHERE estado = 'activo' ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener usuarios con filtros
     */
    public function obtenerConFiltros($filtros = []) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        // Filtro de búsqueda (nombre o correo)
        if (!empty($filtros['buscar'])) {
            $query .= " AND (nombre ILIKE :buscar OR apellidos ILIKE :buscar OR correo_institucional ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        // Filtro de cargo
        if (!empty($filtros['cargo'])) {
            $query .= " AND cargo ILIKE :cargo";
            $params[':cargo'] = '%' . $filtros['cargo'] . '%';
        }

        // Filtro de tipo usuario
        if (!empty($filtros['tipo'])) {
            $query .= " AND tipo_usuario = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        // Filtro de estado
        if (!empty($filtros['estado'])) {
            $query .= " AND estado = :estado";
            $params[':estado'] = $filtros['estado'];
        } else {
            // Por defecto mostrar activos
            $query .= " AND estado = 'activo'";
        }

        $query .= " ORDER BY nombre ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener usuario por correo
     */
    public function obtenerPorCorreo($correo) {
        $query = "SELECT * FROM {$this->table} WHERE correo_institucional = :correo";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crear nuevo usuario
     */
    public function crear() {
        $query = "INSERT INTO {$this->table} 
                  (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña) 
                  VALUES 
                  (:nombre, :apellidos, :correo_institucional, :cargo, :tipo_usuario, :pass)";
        
        $stmt = $this->db->prepare($query);

        // Encriptar contraseña
        $contraseña_encriptada = password_hash($this->contraseña, PASSWORD_BCRYPT);

        // Bind
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellidos', $this->apellidos);
        $stmt->bindParam(':correo_institucional', $this->correo_institucional);
        $stmt->bindParam(':cargo', $this->cargo);
        $stmt->bindParam(':tipo_usuario', $this->tipo_usuario);
        $stmt->bindParam(':pass', $contraseña_encriptada);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Actualizar usuario
     */
    public function actualizar() {
        $query = "UPDATE {$this->table} 
                  SET nombre = :nombre, 
                      apellidos = :apellidos, 
                      cargo = :cargo, 
                      tipo_usuario = :tipo_usuario,
                      estado = :estado,
                      fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);

        // Bind
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellidos', $this->apellidos);
        $stmt->bindParam(':cargo', $this->cargo);
        $stmt->bindParam(':tipo_usuario', $this->tipo_usuario);
        $stmt->bindParam(':estado', $this->estado);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarContraseña($id, $nueva_contraseña) {
        $query = "UPDATE {$this->table} 
                  SET contraseña = :pass,
                      fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        // Encriptar nueva contraseña
        $contraseña_encriptada = password_hash($nueva_contraseña, PASSWORD_BCRYPT);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':pass', $contraseña_encriptada);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Verificar contraseña
     */
    public function verificarContraseña($contraseña, $hash_contraseña) {
        return password_verify($contraseña, $hash_contraseña);
    }

    /**
     * Eliminar usuario (cambiar estado)
     */
    public function eliminar($id) {
        $query = "UPDATE {$this->table} 
                  SET estado = 'inactivo',
                      fecha_actualizacion = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Autenticar usuario
     */
    public function autenticar($correo, $contraseña) {
        $usuario = $this->obtenerPorCorreo($correo);
        
        if ($usuario && $this->verificarContraseña($contraseña, $usuario['contraseña'])) {
            return $usuario;
        }
        return null;
    }

    /**
     * Obtener certificados creados por un usuario
     */
    public function obtenerCertificados($usuario_id) {
        $query = "SELECT * FROM certificados WHERE usuario_id = :usuario_id ORDER BY fecha_creacion DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener cantidad de certificados creados
     */
    public function obtenerCantidadCertificados($usuario_id) {
        $query = "SELECT COUNT(*) as total FROM certificados WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Obtener nombre completo del usuario
     */
    public function getNombreCompleto() {
        return "{$this->nombre} {$this->apellidos}";
    }
}
