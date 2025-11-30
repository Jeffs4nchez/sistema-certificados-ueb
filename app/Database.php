<?php
/**
 * Clase Database - Singleton para PDO PostgreSQL
 * Gestiona la conexión a la base de datos
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Configuración de conexión PostgreSQL
    private $host = 'localhost';
    private $port = '5432';
    private $user = 'postgres';
    private $pass = 'jeffo2003';
    private $database = 'certificados_sistema';
    
    /**
     * Constructor privado para el patrón Singleton
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Obtener la instancia única de la clase
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Conectar a la base de datos usando PDO
     */
    private function connect() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database};";
            $this->connection = new PDO(
                $dsn,
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener la conexión
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Cerrar la conexión
     */
    public function closeConnection() {
        $this->connection = null;
    }
    
    /**
     * Evitar clonar la instancia
     */
    public function __clone() {}
    
    /**
     * Evitar deserializar la instancia
     */
    public function __wakeup() {}
}
?>
