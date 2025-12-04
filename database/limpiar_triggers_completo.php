<?php
/**
 * Script para listar y limpiar TODOS los triggers duplicados/antiguos
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
    
    // Listar todos los triggers en detalle_certificados
    echo "=== LISTANDO TODOS LOS TRIGGERS EXISTENTES ===\n";
    $result = pg_query($conn, "
        SELECT trigger_name, event_manipulation, action_timing 
        FROM information_schema.triggers 
        WHERE trigger_schema = 'public' AND event_object_table = 'detalle_certificados'
        ORDER BY trigger_name
    ");
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            echo "  - " . $row['trigger_name'] . " (" . $row['event_manipulation'] . " " . $row['action_timing'] . ")\n";
        }
    }
    
    echo "\n=== ELIMINANDO TODOS LOS TRIGGERS ===\n";
    
    // Obtener todos los nombres de triggers
    $result = pg_query($conn, "
        SELECT trigger_name 
        FROM information_schema.triggers 
        WHERE trigger_schema = 'public' AND event_object_table = 'detalle_certificados'
    ");
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $trigger_name = $row['trigger_name'];
            $drop_cmd = "DROP TRIGGER IF EXISTS " . $trigger_name . " ON detalle_certificados CASCADE;";
            $drop_result = pg_query($conn, $drop_cmd);
            if ($drop_result) {
                echo "[✓] Eliminado: " . $trigger_name . "\n";
            } else {
                echo "[✗] Error al eliminar " . $trigger_name . ": " . pg_last_error($conn) . "\n";
            }
        }
    }
    
    echo "\n=== ELIMINANDO TODAS LAS FUNCIONES ===\n";
    
    // Obtener todas las funciones relacionadas con triggers
    $result = pg_query($conn, "
        SELECT routine_name 
        FROM information_schema.routines 
        WHERE routine_schema = 'public' 
        AND (routine_name LIKE 'trg_%' OR routine_name LIKE 'trigger_%')
    ");
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $func_name = $row['routine_name'];
            $drop_cmd = "DROP FUNCTION IF EXISTS " . $func_name . "() CASCADE;";
            $drop_result = pg_query($conn, $drop_cmd);
            if ($drop_result) {
                echo "[✓] Eliminada: " . $func_name . "()\n";
            } else {
                echo "[✗] Error: " . pg_last_error($conn) . "\n";
            }
        }
    }
    
    echo "\n=== CREANDO TRIGGERS NUEVOS Y LIMPIOS ===\n";
    
    // Crear trigger INSERT
    $insert_trigger = "
CREATE FUNCTION trg_sync_col4_on_insert()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = NEW.monto,
        col8 = COALESCE(col1, 0) - NEW.monto,
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
    
    // Crear trigger UPDATE
    $update_trigger = "
CREATE FUNCTION trg_sync_col4_on_update()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = NEW.monto,
        col8 = COALESCE(col1, 0) - NEW.monto,
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
    
    // Crear trigger DELETE
    $delete_trigger = "
CREATE FUNCTION trg_sync_col4_on_delete()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = 0,
        col8 = COALESCE(col1, 0),
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
            echo "  ✓ " . $row['trigger_name'] . "\n";
            $count++;
        }
        echo "\nTotal de triggers finales: $count\n";
    }
    
    echo "\n[✓✓✓] Limpieza completa y triggers recreados\n";
    
    pg_close($conn);
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage();
}
?>
