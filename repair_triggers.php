<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║  REPARAR TRIGGERS: Eliminar Viejos e Instalar Nuevos      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

try {
    // PASO 1: Eliminar todos los triggers y funciones antiguas
    echo "📌 PASO 1: Limpiar triggers y funciones antiguas...\n";
    
    $oldTriggers = [
        'trigger_certificados_actualiza_col4',
        'trigger_liquidaciones_actualiza_col4',
        'trigger_col4_recalcula_saldo'
    ];
    
    $oldFunctions = [
        'fn_trigger_certificados_actualiza_col4',
        'fn_trigger_liquidaciones_actualiza_col4',
        'fn_trigger_col4_recalcula_saldo'
    ];
    
    // Eliminar triggers primero (dependen de funciones)
    foreach ($oldTriggers as $trigger) {
        $db->exec("DROP TRIGGER IF EXISTS $trigger ON certificados CASCADE");
        $db->exec("DROP TRIGGER IF EXISTS $trigger ON detalle_certificados CASCADE");
        $db->exec("DROP TRIGGER IF EXISTS $trigger ON presupuesto_items CASCADE");
        echo "  ✓ Eliminado trigger: $trigger\n";
    }
    
    // Luego eliminar funciones
    foreach ($oldFunctions as $func) {
        $db->exec("DROP FUNCTION IF EXISTS $func() CASCADE");
        echo "  ✓ Eliminada función: $func\n";
    }
    
    echo "\n✅ Triggers y funciones antiguas eliminadas\n\n";
    
    // PASO 2: Crear nuevos triggers
    echo "📌 PASO 2: Crear nuevos triggers...\n\n";
    
    // TRIGGER 1: INSERT en detalle_certificados
    echo "  [1/3] Creando trigger_detalle_insert_col4...\n";
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_insert_col4()
        RETURNS TRIGGER AS \$\$
        BEGIN
            UPDATE presupuesto_items
            SET col4 = COALESCE(col4, 0) + NEW.monto,
                fecha_actualizacion = NOW()
            WHERE codigo_completo = NEW.codigo_completo;
            
            RETURN NEW;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    
    $db->exec("
        DROP TRIGGER IF EXISTS trigger_detalle_insert_col4 ON detalle_certificados CASCADE;
        CREATE TRIGGER trigger_detalle_insert_col4
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_trigger_detalle_insert_col4();
    ");
    echo "  ✓ trigger_detalle_insert_col4 creado\n";
    
    // TRIGGER 2: UPDATE cantidad_liquidacion en detalle_certificados
    echo "  [2/3] Creando trigger_detalle_update_col4...\n";
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_update_col4()
        RETURNS TRIGGER AS \$\$
        DECLARE
            diferencia NUMERIC;
        BEGIN
            IF OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion THEN
                diferencia := NEW.cantidad_liquidacion - COALESCE(OLD.cantidad_liquidacion, 0);
                
                UPDATE presupuesto_items
                SET col4 = COALESCE(col4, 0) + diferencia,
                    fecha_actualizacion = NOW()
                WHERE codigo_completo = NEW.codigo_completo;
            END IF;
            
            RETURN NEW;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    
    $db->exec("
        DROP TRIGGER IF EXISTS trigger_detalle_update_col4 ON detalle_certificados CASCADE;
        CREATE TRIGGER trigger_detalle_update_col4
        AFTER UPDATE ON detalle_certificados
        FOR EACH ROW
        WHEN (OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion)
        EXECUTE FUNCTION fn_trigger_detalle_update_col4();
    ");
    echo "  ✓ trigger_detalle_update_col4 creado\n";
    
    // TRIGGER 3: DELETE en detalle_certificados
    echo "  [3/3] Creando trigger_detalle_delete_col4...\n";
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_delete_col4()
        RETURNS TRIGGER AS \$\$
        BEGIN
            UPDATE presupuesto_items
            SET col4 = COALESCE(col4, 0) - OLD.monto,
                fecha_actualizacion = NOW()
            WHERE codigo_completo = OLD.codigo_completo;
            
            RETURN OLD;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    
    $db->exec("
        DROP TRIGGER IF EXISTS trigger_detalle_delete_col4 ON detalle_certificados CASCADE;
        CREATE TRIGGER trigger_detalle_delete_col4
        AFTER DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_trigger_detalle_delete_col4();
    ");
    echo "  ✓ trigger_detalle_delete_col4 creado\n";
    
    // TRIGGER 4: BEFORE UPDATE presupuesto_items para recalcular saldo_disponible
    echo "  [4/4] Creando trigger_col4_recalcula_saldo...\n";
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_col4_recalcula_saldo()
        RETURNS TRIGGER AS \$\$
        BEGIN
            NEW.saldo_disponible := COALESCE(NEW.col3, 0) - COALESCE(NEW.col4, 0);
            NEW.fecha_actualizacion := NOW();
            RETURN NEW;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    
    $db->exec("
        DROP TRIGGER IF EXISTS trigger_col4_recalcula_saldo ON presupuesto_items CASCADE;
        CREATE TRIGGER trigger_col4_recalcula_saldo
        BEFORE UPDATE ON presupuesto_items
        FOR EACH ROW
        WHEN (OLD.col4 IS DISTINCT FROM NEW.col4)
        EXECUTE FUNCTION fn_trigger_col4_recalcula_saldo();
    ");
    echo "  ✓ trigger_col4_recalcula_saldo creado\n";
    
    echo "\n✅ Todos los nuevos triggers instalados correctamente\n\n";
    
    // PASO 3: Recalcular col4 para items existentes
    echo "📌 PASO 3: Recalcular col4 para certificados existentes...\n";
    
    $db->exec("
        UPDATE presupuesto_items pi
        SET col4 = (
            SELECT COALESCE(SUM(dc.monto), 0)
            FROM detalle_certificados dc
            WHERE dc.codigo_completo = pi.codigo_completo
        ),
        saldo_disponible = COALESCE(col3, 0) - (
            SELECT COALESCE(SUM(dc.monto), 0)
            FROM detalle_certificados dc
            WHERE dc.codigo_completo = pi.codigo_completo
        )
    ");
    
    echo "  ✓ col4 recalculado para todos los items\n";
    
    echo "\n╔══════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ REPARACIÓN COMPLETADA EXITOSAMENTE                      ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
}
?>
