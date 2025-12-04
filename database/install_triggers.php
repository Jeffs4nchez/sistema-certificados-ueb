<?php
/**
 * Script para inicializar triggers de MySQL y actualizar valores de liquidación
 * Este script se ejecuta una sola vez automáticamente
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'certificados_sistema');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        die("[ERROR] Conexión fallida: " . $conn->connect_error);
    }
    
    echo "[INFO] Conectado a la base de datos\n";
    
    // ============================================================
    // Paso 1: Crear triggers MySQL
    // ============================================================
    
    $sql_triggers = "
DELIMITER //

-- TRIGGER 1: Al INSERT
DROP TRIGGER IF EXISTS trg_update_liquidado_insert //

CREATE TRIGGER trg_update_liquidado_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = NEW.certificado_id
        ), 0),
        total_pendiente = monto_total - COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = NEW.certificado_id
        ), 0)
    WHERE id = NEW.certificado_id;
END //

-- TRIGGER 2: Al UPDATE
DROP TRIGGER IF EXISTS trg_update_liquidado_update //

CREATE TRIGGER trg_update_liquidado_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = NEW.certificado_id
        ), 0),
        total_pendiente = monto_total - COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = NEW.certificado_id
        ), 0)
    WHERE id = NEW.certificado_id;
END //

-- TRIGGER 3: Al DELETE
DROP TRIGGER IF EXISTS trg_update_liquidado_delete //

CREATE TRIGGER trg_update_liquidado_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = OLD.certificado_id
        ), 0),
        total_pendiente = monto_total - COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = OLD.certificado_id
        ), 0)
    WHERE id = OLD.certificado_id;
END //

DELIMITER ;
    ";
    
    // Ejecutar cada trigger por separado (DELIMITER no funciona bien con multi-query)
    $triggers = [
        "
DROP TRIGGER IF EXISTS trg_update_liquidado_insert;

CREATE TRIGGER trg_update_liquidado_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = NEW.certificado_id
        ), 0),
        total_pendiente = monto_total
    WHERE id = NEW.certificado_id;
END;
        ",
        "
DROP TRIGGER IF EXISTS trg_update_liquidado_update;

CREATE TRIGGER trg_update_liquidado_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = NEW.certificado_id
        ), 0),
        total_pendiente = monto_total
    WHERE id = NEW.certificado_id;
END;
        ",
        "
DROP TRIGGER IF EXISTS trg_update_liquidado_delete;

CREATE TRIGGER trg_update_liquidado_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = OLD.certificado_id
        ), 0),
        total_pendiente = monto_total
    WHERE id = OLD.certificado_id;
END;
        "
    ];
    
    foreach ($triggers as $i => $trigger_sql) {
        if ($conn->query($trigger_sql)) {
            echo "[OK] Trigger " . ($i + 1) . " creado correctamente\n";
        } else {
            echo "[ERROR] Trigger " . ($i + 1) . ": " . $conn->error . "\n";
        }
    }
    
    // ============================================================
    // Paso 2: Actualizar valores iniciales
    // ============================================================
    
    $update_sql = "
        UPDATE certificados c
        SET 
            total_liquidado = COALESCE((
                SELECT SUM(dc.cantidad_liquidacion)
                FROM detalle_certificados dc
                WHERE dc.certificado_id = c.id
            ), 0),
            total_pendiente = c.monto_total;
    ";
    
    if ($conn->query($update_sql)) {
        // Obtener cantidad de filas actualizadas
        $result = $conn->query("SELECT COUNT(*) as total FROM certificados");
        $row = $result->fetch_assoc();
        echo "[OK] Se inicializaron correctamente los valores de " . $row['total'] . " certificados\n";
    } else {
        echo "[ERROR] Error al actualizar valores iniciales: " . $conn->error . "\n";
    }
    
    $conn->close();
    echo "[OK] Instalación completada exitosamente\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
?>
