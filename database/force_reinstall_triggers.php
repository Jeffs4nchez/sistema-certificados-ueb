<?php
/**
 * Script para FORZAR la reinstalación de triggers de PostgreSQL
 * Elimina los triggers antiguos y crea los nuevos
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
    // Paso 1: ELIMINAR todos los triggers antiguos relacionados con presupuesto
    // ============================================================
    
    $drop_triggers = [
        // Triggers antiguos de col4
        "DROP TRIGGER IF EXISTS trg_sync_col4_on_insert ON detalle_certificados CASCADE;",
        "DROP TRIGGER IF EXISTS trg_sync_col4_on_update ON detalle_certificados CASCADE;",
        "DROP TRIGGER IF EXISTS trg_sync_col4_on_delete ON detalle_certificados CASCADE;",
        
        // Triggers que actualizan liquidaciones (col7)
        "DROP TRIGGER IF EXISTS trigger_insert_detalle_certificados ON detalle_certificados CASCADE;",
        "DROP TRIGGER IF EXISTS trigger_update_liquidacion ON detalle_certificados CASCADE;",
        "DROP TRIGGER IF EXISTS trigger_delete_detalle_certificados ON detalle_certificados CASCADE;",
        
        // Triggers de certificados (total_liquidado y total_pendiente)
        "DROP TRIGGER IF EXISTS trg_update_liquidado_insert ON detalle_certificados CASCADE;",
        "DROP TRIGGER IF EXISTS trg_update_liquidado_update ON detalle_certificados CASCADE;",
        "DROP TRIGGER IF EXISTS trg_update_liquidado_delete ON detalle_certificados CASCADE;",
        
        // Funciones asociadas
        "DROP FUNCTION IF EXISTS trg_sync_col4_on_insert() CASCADE;",
        "DROP FUNCTION IF EXISTS trg_sync_col4_on_update() CASCADE;",
        "DROP FUNCTION IF EXISTS trg_sync_col4_on_delete() CASCADE;",
        "DROP FUNCTION IF EXISTS trigger_insert_detalle_certificados() CASCADE;",
        "DROP FUNCTION IF EXISTS trigger_update_liquidacion() CASCADE;",
        "DROP FUNCTION IF EXISTS trigger_delete_detalle_certificados() CASCADE;",
        "DROP FUNCTION IF EXISTS trg_update_liquidado_insert() CASCADE;",
        "DROP FUNCTION IF EXISTS trg_update_liquidado_update() CASCADE;",
        "DROP FUNCTION IF EXISTS trg_update_liquidado_delete() CASCADE;"
    ];
    
    echo "\n[INFO] === ELIMINANDO TRIGGERS ANTIGUOS ===\n";
    foreach ($drop_triggers as $drop_sql) {
        $result = pg_query($conn, $drop_sql);
        if ($result) {
            echo "[✓] " . trim($drop_sql) . "\n";
        } else {
            // Silenciosamente ignorar errores de "no existe"
        }
    }
    
    // ============================================================
    // Paso 2: CREAR SOLO el trigger necesario para col4
    // ============================================================
    
    echo "\n[INFO] === CREANDO TRIGGERS NUEVOS ===\n";
    
    $create_triggers = [
        // TRIGGER 1: INSERT - Guarda el monto individual SIN SUMAR
        "
CREATE FUNCTION trg_sync_col4_on_insert()
RETURNS TRIGGER AS \$\$
BEGIN
    -- Actualiza col4 en presupuesto_items con el monto del item (sin sumar)
    UPDATE presupuesto_items
    SET col4 = NEW.monto,
        col8 = COALESCE(col1, 0) - NEW.monto,
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sync_col4_on_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_insert();
        ",
        // TRIGGER 2: UPDATE - Actualiza el monto sin sumar
        "
CREATE FUNCTION trg_sync_col4_on_update()
RETURNS TRIGGER AS \$\$
BEGIN
    -- Actualiza col4 con el nuevo monto
    UPDATE presupuesto_items
    SET col4 = NEW.monto,
        col8 = COALESCE(col1, 0) - NEW.monto,
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sync_col4_on_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_update();
        ",
        // TRIGGER 3: DELETE - Limpia col4
        "
CREATE FUNCTION trg_sync_col4_on_delete()
RETURNS TRIGGER AS \$\$
BEGIN
    -- Limpia col4 y col8 cuando se elimina el item
    UPDATE presupuesto_items
    SET col4 = 0,
        col8 = COALESCE(col1, 0),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = OLD.codigo_completo;
    RETURN OLD;
END;
\$\$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sync_col4_on_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_delete();
        "
    ];
    
    foreach ($create_triggers as $i => $trigger_sql) {
        $result = pg_query($conn, $trigger_sql);
        if ($result) {
            echo "[✓] Trigger " . ($i + 1) . " creado correctamente\n";
        } else {
            echo "[ERROR] Trigger " . ($i + 1) . ": " . pg_last_error($conn) . "\n";
        }
    }
    
    pg_close($conn);
    echo "[✓] Triggers reinstalados exitosamente\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
?>
