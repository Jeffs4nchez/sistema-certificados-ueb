<?php
/**
 * Script para recrear SOLO los triggers de liquidación
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
    
    echo "=== CREANDO TRIGGERS DE LIQUIDACIÓN ===\n";
    
    // Trigger INSERT - Actualiza total_liquidado y total_pendiente en certificados
    $insert_liq = "
CREATE FUNCTION trg_update_liquidado_insert()
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
";
    
    $result = pg_query($conn, $insert_liq);
    if ($result) {
        echo "[✓] Función trg_update_liquidado_insert creada\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    $result = pg_query($conn, "CREATE TRIGGER trg_update_liquidado_insert AFTER INSERT ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_update_liquidado_insert();");
    if ($result) {
        echo "[✓] Trigger trg_update_liquidado_insert creado\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    // Trigger UPDATE - Actualiza liquidaciones
    $update_liq = "
CREATE FUNCTION trg_update_liquidado_update()
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
";
    
    $result = pg_query($conn, $update_liq);
    if ($result) {
        echo "[✓] Función trg_update_liquidado_update creada\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    $result = pg_query($conn, "CREATE TRIGGER trg_update_liquidado_update AFTER UPDATE ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_update_liquidado_update();");
    if ($result) {
        echo "[✓] Trigger trg_update_liquidado_update creado\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    // Trigger DELETE - Actualiza liquidaciones al eliminar
    $delete_liq = "
CREATE FUNCTION trg_update_liquidado_delete()
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
";
    
    $result = pg_query($conn, $delete_liq);
    if ($result) {
        echo "[✓] Función trg_update_liquidado_delete creada\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    $result = pg_query($conn, "CREATE TRIGGER trg_update_liquidado_delete AFTER DELETE ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_update_liquidado_delete();");
    if ($result) {
        echo "[✓] Trigger trg_update_liquidado_delete creado\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
    
    echo "\n=== VERIFICACIÓN FINAL ===\n";
    $result = pg_query($conn, "
        SELECT trigger_name, event_manipulation, action_timing 
        FROM information_schema.triggers 
        WHERE trigger_schema = 'public' AND event_object_table = 'detalle_certificados'
        ORDER BY trigger_name
    ");
    
    if ($result) {
        $count = 0;
        while ($row = pg_fetch_assoc($result)) {
            echo "  ✓ " . $row['trigger_name'] . " (" . $row['event_manipulation'] . ")\n";
            $count++;
        }
        echo "\nTotal de triggers: $count\n";
    }
    
    echo "\n[✓✓✓] Triggers de liquidación recreados\n";
    
    pg_close($conn);
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage();
}
?>
