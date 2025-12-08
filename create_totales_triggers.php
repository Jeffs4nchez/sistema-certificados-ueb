<?php
/**
 * CREAR TRIGGER: Actualizar certificados.total_liquidado/total_pendiente
 * cuando se inserta o actualiza un item en detalle_certificados
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "CREAR TRIGGER: Actualizar totales en certificados\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. CREAR FUNCIÓN QUE ACTUALIZA TOTALES
    echo "1️⃣  CREANDO FUNCIÓN trigger_update_certificados_totales...\n";
    echo str_repeat("-", 80) . "\n";
    
    $fnSQL = "
        CREATE OR REPLACE FUNCTION trigger_update_certificados_totales()
        RETURNS TRIGGER AS \$\$
        BEGIN
            -- Recalcular totales del certificado después de INSERT o UPDATE
            UPDATE certificados
            SET 
                total_liquidado = COALESCE((
                    SELECT SUM(cantidad_liquidacion)
                    FROM detalle_certificados
                    WHERE certificado_id = NEW.certificado_id
                ), 0),
                total_pendiente = COALESCE((
                    SELECT SUM(cantidad_pendiente)
                    FROM detalle_certificados
                    WHERE certificado_id = NEW.certificado_id
                ), 0),
                fecha_actualizacion = NOW()
            WHERE id = NEW.certificado_id;
            
            RETURN NEW;
        END;
        \$\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($fnSQL);
    echo "✓ Función creada\n\n";
    
    // 2. CREAR TRIGGER PARA INSERT
    echo "2️⃣  CREANDO TRIGGER para INSERT...\n";
    echo str_repeat("-", 80) . "\n";
    
    $triggerInsertSQL = "
        DROP TRIGGER IF EXISTS trg_update_cert_totales_insert ON detalle_certificados CASCADE;
        CREATE TRIGGER trg_update_cert_totales_insert
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION trigger_update_certificados_totales();
    ";
    
    $db->exec($triggerInsertSQL);
    echo "✓ Trigger INSERT creado\n\n";
    
    // 3. CREAR TRIGGER PARA UPDATE
    echo "3️⃣  CREANDO TRIGGER para UPDATE...\n";
    echo str_repeat("-", 80) . "\n";
    
    $triggerUpdateSQL = "
        DROP TRIGGER IF EXISTS trg_update_cert_totales_update ON detalle_certificados CASCADE;
        CREATE TRIGGER trg_update_cert_totales_update
        AFTER UPDATE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION trigger_update_certificados_totales();
    ";
    
    $db->exec($triggerUpdateSQL);
    echo "✓ Trigger UPDATE creado\n\n";
    
    // 4. CREAR TRIGGER PARA DELETE
    echo "4️⃣  CREANDO TRIGGER para DELETE...\n";
    echo str_repeat("-", 80) . "\n";
    
    $triggerDeleteSQL = "
        CREATE OR REPLACE FUNCTION trigger_update_certificados_totales_delete()
        RETURNS TRIGGER AS \$\$
        BEGIN
            -- Recalcular totales del certificado después de DELETE
            UPDATE certificados
            SET 
                total_liquidado = COALESCE((
                    SELECT SUM(cantidad_liquidacion)
                    FROM detalle_certificados
                    WHERE certificado_id = OLD.certificado_id
                ), 0),
                total_pendiente = COALESCE((
                    SELECT SUM(cantidad_pendiente)
                    FROM detalle_certificados
                    WHERE certificado_id = OLD.certificado_id
                ), 0),
                fecha_actualizacion = NOW()
            WHERE id = OLD.certificado_id;
            
            RETURN OLD;
        END;
        \$\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($triggerDeleteSQL);
    
    $triggerDeleteExecSQL = "
        DROP TRIGGER IF EXISTS trg_update_cert_totales_delete ON detalle_certificados CASCADE;
        CREATE TRIGGER trg_update_cert_totales_delete
        BEFORE DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION trigger_update_certificados_totales_delete();
    ";
    
    $db->exec($triggerDeleteExecSQL);
    echo "✓ Trigger DELETE creado\n\n";
    
    // 5. VERIFICAR QUE LOS TRIGGERS EXISTEN
    echo "5️⃣  VERIFICANDO TRIGGERS...\n";
    echo str_repeat("-", 80) . "\n";
    
    $checkStmt = $db->query("
        SELECT trigger_name, event_manipulation, action_timing
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
          AND trigger_name LIKE 'trg_update_cert_totales%'
        ORDER BY trigger_name
    ");
    
    $triggers = $checkStmt->fetchAll();
    
    if (empty($triggers)) {
        echo "⚠️  NO SE ENCONTRARON TRIGGERS\n";
    } else {
        echo "Triggers encontrados:\n";
        foreach ($triggers as $trg) {
            echo "  ✓ " . $trg['trigger_name'] . " (" . $trg['action_timing'] . " " . $trg['event_manipulation'] . ")\n";
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ TRIGGERS CREADOS EXITOSAMENTE\n";
    echo str_repeat("=", 80) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
