<?php
/**
 * REPARAR TRIGGERS - Eliminar conflictos y crear nuevos correctamente
 */

$host = 'localhost';
$port = '5432';
$database = 'certificados_sistema';
$user = 'postgres';
$pass = 'jeffo2003';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "REPARACIÓN DE TRIGGERS\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // PASO 1: ELIMINAR TODOS LOS TRIGGERS ANTIGUOS
    echo "1️⃣ ELIMINANDO TRIGGERS CONFLICTIVOS...\n";
    echo str_repeat("-", 80) . "\n";
    
    $triggers_to_drop = [
        'trigger_actualiza_total_pendiente_delete',
        'trigger_actualiza_total_pendiente_insert',
        'trigger_actualiza_total_pendiente_update',
        'trigger_delete_col4',
        'trigger_insert_col4',
        'trigger_recalcula_pendiente',
        'trigger_update_col4_consolidado',
        'trigger_detalle_insert',
        'trigger_detalle_update',
        'trigger_detalle_delete'
    ];
    
    foreach ($triggers_to_drop as $trigger) {
        try {
            $db->exec("DROP TRIGGER IF EXISTS {$trigger} ON detalle_certificados CASCADE");
            echo "   ✓ {$trigger} eliminado\n";
        } catch (Exception $e) {
            echo "   ⊘ {$trigger} no existía\n";
        }
    }
    
    // PASO 2: ELIMINAR FUNCIONES ANTIGUAS
    echo "\n2️⃣ ELIMINANDO FUNCIONES ANTIGUAS...\n";
    echo str_repeat("-", 80) . "\n";
    
    $functions_to_drop = [
        'fn_trigger_detalle_insert()',
        'fn_trigger_detalle_update()',
        'fn_trigger_detalle_delete()',
        'trigger_actualiza_total_pendiente_delete()',
        'trigger_actualiza_total_pendiente_insert()',
        'trigger_actualiza_total_pendiente_update()',
        'trigger_delete_col4()',
        'trigger_insert_col4()',
        'trigger_recalcula_pendiente()',
        'trigger_update_col4_consolidado()'
    ];
    
    foreach ($functions_to_drop as $func) {
        try {
            $db->exec("DROP FUNCTION IF EXISTS {$func} CASCADE");
            echo "   ✓ {$func} eliminada\n";
        } catch (Exception $e) {
            echo "   ⊘ {$func} no existía\n";
        }
    }
    
    echo "\n3️⃣ CREANDO NUEVOS TRIGGERS CORRECTAMENTE...\n";
    echo str_repeat("-", 80) . "\n";
    
    // TRIGGER 1: INSERT
    echo "   Creando trigger INSERT...\n";
    $sql_1 = "
    CREATE FUNCTION fn_trigger_item_insert() RETURNS TRIGGER AS \$func\$
    BEGIN
        -- Actualizar presupuesto_items al insertar nuevo item
        UPDATE presupuesto_items
        SET col4 = COALESCE(col4, 0) + NEW.monto,
            col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) + NEW.monto) - COALESCE(col5, 0) - COALESCE(col6, 0) - COALESCE(col7, 0),
            fecha_actualizacion = NOW()
        WHERE codigo_completo = NEW.codigo_completo;
        
        RETURN NEW;
    END;
    \$func\$ LANGUAGE plpgsql;
    
    CREATE TRIGGER trg_item_insert
    AFTER INSERT ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_item_insert();
    ";
    
    try {
        $db->exec($sql_1);
        echo "   ✅ Trigger INSERT creado\n";
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    // TRIGGER 2: UPDATE
    echo "   Creando trigger UPDATE...\n";
    $sql_2 = "
    CREATE FUNCTION fn_trigger_item_update() RETURNS TRIGGER AS \$func\$
    DECLARE
        monto_diff NUMERIC;
    BEGIN
        -- Si cambió el monto, actualizar presupuesto_items
        IF NEW.monto <> OLD.monto THEN
            monto_diff := NEW.monto - OLD.monto;
            UPDATE presupuesto_items
            SET col4 = COALESCE(col4, 0) + monto_diff,
                col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) + monto_diff) - COALESCE(col5, 0) - COALESCE(col6, 0) - COALESCE(col7, 0),
                fecha_actualizacion = NOW()
            WHERE codigo_completo = NEW.codigo_completo;
        END IF;
        
        RETURN NEW;
    END;
    \$func\$ LANGUAGE plpgsql;
    
    CREATE TRIGGER trg_item_update
    AFTER UPDATE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_item_update();
    ";
    
    try {
        $db->exec($sql_2);
        echo "   ✅ Trigger UPDATE creado\n";
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    // TRIGGER 3: DELETE
    echo "   Creando trigger DELETE...\n";
    $sql_3 = "
    CREATE FUNCTION fn_trigger_item_delete() RETURNS TRIGGER AS \$func\$
    BEGIN
        -- Actualizar presupuesto_items al eliminar item
        UPDATE presupuesto_items
        SET col4 = COALESCE(col4, 0) - OLD.monto,
            col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) - OLD.monto) - COALESCE(col5, 0) - COALESCE(col6, 0) - COALESCE(col7, 0),
            fecha_actualizacion = NOW()
        WHERE codigo_completo = OLD.codigo_completo;
        
        RETURN OLD;
    END;
    \$func\$ LANGUAGE plpgsql;
    
    CREATE TRIGGER trg_item_delete
    BEFORE DELETE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_item_delete();
    ";
    
    try {
        $db->exec($sql_3);
        echo "   ✅ Trigger DELETE creado\n";
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ REPARACIÓN COMPLETADA\n";
    echo str_repeat("=", 80) . "\n";
    echo "Los triggers han sido reparados y deben funcionar correctamente.\n";
    echo "Próximo paso: ejecuta 'php probar_triggers_items.php' para verificar.\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
