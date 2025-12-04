<?php
/**
 * Script para corregir el trigger de col4 - SUMAR todos los montos para ese codigo_completo
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'postgres');
define('DB_PASS', 'jeffo2003');
define('DB_NAME', 'certificados_sistema');
define('DB_PORT', '5432');

try {
    $conn = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS);
    
    if (!$conn) {
        die("[ERROR] No se pudo conectar: " . pg_last_error());
    }
    
    echo "[✓] Conectado a PostgreSQL\n\n";
    
    echo "=== CORRIGIENDO TRIGGERS DE COL4 ===\n";
    
    // Eliminar triggers antiguos de col4
    $drop_commands = [
        "DROP TRIGGER IF EXISTS trg_sync_col4_on_insert ON detalle_certificados CASCADE;",
        "DROP TRIGGER IF EXISTS trg_sync_col4_on_update ON detalle_certificados CASCADE;",
        "DROP TRIGGER IF EXISTS trg_sync_col4_on_delete ON detalle_certificados CASCADE;",
        "DROP FUNCTION IF EXISTS trg_sync_col4_on_insert() CASCADE;",
        "DROP FUNCTION IF EXISTS trg_sync_col4_on_update() CASCADE;",
        "DROP FUNCTION IF EXISTS trg_sync_col4_on_delete() CASCADE;"
    ];
    
    foreach ($drop_commands as $cmd) {
        $result = pg_query($conn, $cmd);
        if ($result) {
            echo "[✓] " . trim(substr($cmd, 0, 40)) . "...\n";
        }
    }
    
    echo "\n=== CREANDO TRIGGERS NUEVOS ===\n";
    
    // Trigger INSERT - SUMA todos los montos con ese codigo_completo
    $insert_trigger = "
CREATE FUNCTION trg_sync_col4_on_insert()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = (
            SELECT SUM(monto) 
            FROM detalle_certificados 
            WHERE codigo_completo = NEW.codigo_completo
        ),
        col8 = COALESCE(col1, 0) - (
            SELECT SUM(monto) 
            FROM detalle_certificados 
            WHERE codigo_completo = NEW.codigo_completo
        ),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";
    
    $result = pg_query($conn, $insert_trigger);
    if ($result) {
        echo "[✓] Función trg_sync_col4_on_insert creada\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    $result = pg_query($conn, "CREATE TRIGGER trg_sync_col4_on_insert AFTER INSERT ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_sync_col4_on_insert();");
    if ($result) {
        echo "[✓] Trigger trg_sync_col4_on_insert creado\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    // Trigger UPDATE - SUMA todos los montos con ese codigo_completo
    $update_trigger = "
CREATE FUNCTION trg_sync_col4_on_update()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = (
            SELECT SUM(monto) 
            FROM detalle_certificados 
            WHERE codigo_completo = NEW.codigo_completo
        ),
        col8 = COALESCE(col1, 0) - (
            SELECT SUM(monto) 
            FROM detalle_certificados 
            WHERE codigo_completo = NEW.codigo_completo
        ),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";
    
    $result = pg_query($conn, $update_trigger);
    if ($result) {
        echo "[✓] Función trg_sync_col4_on_update creada\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    $result = pg_query($conn, "CREATE TRIGGER trg_sync_col4_on_update AFTER UPDATE ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_sync_col4_on_update();");
    if ($result) {
        echo "[✓] Trigger trg_sync_col4_on_update creado\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    // Trigger DELETE - SUMA todos los montos restantes con ese codigo_completo
    $delete_trigger = "
CREATE FUNCTION trg_sync_col4_on_delete()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = COALESCE((
            SELECT SUM(monto) 
            FROM detalle_certificados 
            WHERE codigo_completo = OLD.codigo_completo
        ), 0),
        col8 = COALESCE(col1, 0) - COALESCE((
            SELECT SUM(monto) 
            FROM detalle_certificados 
            WHERE codigo_completo = OLD.codigo_completo
        ), 0),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = OLD.codigo_completo;
    RETURN OLD;
END;
\$\$ LANGUAGE plpgsql;
";
    
    $result = pg_query($conn, $delete_trigger);
    if ($result) {
        echo "[✓] Función trg_sync_col4_on_delete creada\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    $result = pg_query($conn, "CREATE TRIGGER trg_sync_col4_on_delete AFTER DELETE ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_sync_col4_on_delete();");
    if ($result) {
        echo "[✓] Trigger trg_sync_col4_on_delete creado\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    echo "\n[✓✓✓] Triggers corregidos - ahora SUMAN todos los montos con el mismo codigo_completo\n";
    
    pg_close($conn);
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage();
}
?>
