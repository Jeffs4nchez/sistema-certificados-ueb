<?php
/**
 * Script para inicializar triggers de PostgreSQL y actualizar valores de liquidación
 * Este script se ejecuta una sola vez automáticamente
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'postgres');
define('DB_PASS', 'jeffo2003');
define('DB_NAME', 'certificados_sistema');
define('DB_PORT', '5432');

try {
    // Conectar a PostgreSQL
    $conn = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS);
    
    if (!$conn) {
        die("[ERROR] Conexión fallida a PostgreSQL: " . pg_last_error());
    }
    
    echo "[INFO] Conectado a PostgreSQL\n";
    
    // ============================================================
    // Paso 1: Crear funciones y triggers PostgreSQL
    // ============================================================
    
    $triggers = [
        // TRIGGER 1: INSERT
        "
CREATE OR REPLACE FUNCTION trg_update_liquidado_insert()
RETURNS TRIGGER AS \$\$
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
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_liquidado_insert ON detalle_certificados CASCADE;
CREATE TRIGGER trg_update_liquidado_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_update_liquidado_insert();
        ",
        // TRIGGER 2: UPDATE
        "
CREATE OR REPLACE FUNCTION trg_update_liquidado_update()
RETURNS TRIGGER AS \$\$
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
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_liquidado_update ON detalle_certificados CASCADE;
CREATE TRIGGER trg_update_liquidado_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_update_liquidado_update();
        ",
        // TRIGGER 3: DELETE
        "
CREATE OR REPLACE FUNCTION trg_update_liquidado_delete()
RETURNS TRIGGER AS \$\$
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
    RETURN OLD;
END;
\$\$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_liquidado_delete ON detalle_certificados CASCADE;
CREATE TRIGGER trg_update_liquidado_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_update_liquidado_delete();
        "
    ];
    
    foreach ($triggers as $i => $trigger_sql) {
        $result = pg_query($conn, $trigger_sql);
        if ($result) {
            echo "[OK] Trigger " . ($i + 1) . " creado correctamente\n";
        } else {
            echo "[ERROR] Trigger " . ($i + 1) . ": " . pg_last_error($conn) . "\n";
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
    
    $result = pg_query($conn, $update_sql);
    if ($result) {
        // Obtener cantidad de filas afectadas
        $rows = pg_affected_rows($result);
        echo "[OK] Se inicializaron correctamente los valores de $rows certificados\n";
    } else {
        echo "[ERROR] Error al actualizar valores iniciales: " . pg_last_error($conn) . "\n";
    }
    
    pg_close($conn);
    echo "[OK] Instalación completada exitosamente\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
?>
