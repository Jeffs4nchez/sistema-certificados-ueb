<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  Solución Simplificada: Usar col4 correcto              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // PASO 1: Recalcular col4 correctamente
    echo "📌 PASO 1: Recalcular col4 = SUM(monto) de detalle_certificados...\n";
    
    $db->exec("
        UPDATE presupuesto_items pi
        SET col4 = COALESCE((
            SELECT SUM(dc.monto)
            FROM detalle_certificados dc
            WHERE dc.codigo_completo = pi.codigo_completo
        ), 0),
        saldo_disponible = COALESCE(col3, 0) - COALESCE((
            SELECT SUM(dc.monto)
            FROM detalle_certificados dc
            WHERE dc.codigo_completo = pi.codigo_completo
        ), 0),
        fecha_actualizacion = NOW()
    ");
    
    echo "  ✓ col4 recalculado correctamente\n\n";
    
    // PASO 2: Recrear los triggers para que trabajen correctamente
    echo "📌 PASO 2: Recrear triggers con lógica correcta...\n\n";
    
    // Eliminar triggers viejos
    $db->exec("DROP TRIGGER IF EXISTS trigger_detalle_insert_col4 ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trigger_detalle_update_col4 ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trigger_detalle_delete_col4 ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trigger_col4_recalcula_saldo ON presupuesto_items CASCADE");
    
    $db->exec("DROP FUNCTION IF EXISTS fn_trigger_detalle_insert_col4() CASCADE");
    $db->exec("DROP FUNCTION IF EXISTS fn_trigger_detalle_update_col4() CASCADE");
    $db->exec("DROP FUNCTION IF EXISTS fn_trigger_detalle_delete_col4() CASCADE");
    $db->exec("DROP FUNCTION IF EXISTS fn_trigger_col4_recalcula_saldo() CASCADE");
    
    echo "  ✓ Triggers y funciones antiguas eliminados\n\n";
    
    // TRIGGER 1: INSERT - Recalcular col4 con SUM
    echo "  [1/4] Creando trigger INSERT...\n";
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_insert_col4()
        RETURNS TRIGGER AS \$\$
        BEGIN
            UPDATE presupuesto_items
            SET col4 = COALESCE((
                SELECT SUM(dc.monto)
                FROM detalle_certificados dc
                WHERE dc.codigo_completo = NEW.codigo_completo
            ), 0),
            saldo_disponible = COALESCE(col3, 0) - COALESCE((
                SELECT SUM(dc.monto)
                FROM detalle_certificados dc
                WHERE dc.codigo_completo = NEW.codigo_completo
            ), 0),
            fecha_actualizacion = NOW()
            WHERE codigo_completo = NEW.codigo_completo;
            
            RETURN NEW;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    
    $db->exec("
        CREATE TRIGGER trigger_detalle_insert_col4
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_trigger_detalle_insert_col4();
    ");
    echo "  ✓ trigger_detalle_insert_col4 creado\n";
    
    // TRIGGER 2: UPDATE cantidad_liquidacion - Solo recalcular saldo_disponible
    echo "  [2/4] Creando trigger UPDATE...\n";
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_update_col4()
        RETURNS TRIGGER AS \$\$
        BEGIN
            IF OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion THEN
                -- Cuando cambia cantidad_liquidacion, solo recalculamos saldo_disponible
                UPDATE presupuesto_items
                SET saldo_disponible = COALESCE(col3, 0) - COALESCE(col4, 0),
                    fecha_actualizacion = NOW()
                WHERE codigo_completo = NEW.codigo_completo;
            END IF;
            
            RETURN NEW;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    
    $db->exec("
        CREATE TRIGGER trigger_detalle_update_col4
        AFTER UPDATE ON detalle_certificados
        FOR EACH ROW
        WHEN (OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion)
        EXECUTE FUNCTION fn_trigger_detalle_update_col4();
    ");
    echo "  ✓ trigger_detalle_update_col4 creado\n";
    
    // TRIGGER 3: DELETE - Recalcular col4 con SUM (sin el item borrado)
    echo "  [3/4] Creando trigger DELETE...\n";
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_delete_col4()
        RETURNS TRIGGER AS \$\$
        BEGIN
            -- Al borrar, recalculamos col4 con SUM de los items restantes
            UPDATE presupuesto_items
            SET col4 = COALESCE((
                SELECT SUM(dc.monto)
                FROM detalle_certificados dc
                WHERE dc.codigo_completo = OLD.codigo_completo
            ), 0),
            saldo_disponible = COALESCE(col3, 0) - COALESCE((
                SELECT SUM(dc.monto)
                FROM detalle_certificados dc
                WHERE dc.codigo_completo = OLD.codigo_completo
            ), 0),
            fecha_actualizacion = NOW()
            WHERE codigo_completo = OLD.codigo_completo;
            
            RETURN OLD;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    
    $db->exec("
        CREATE TRIGGER trigger_detalle_delete_col4
        AFTER DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_trigger_detalle_delete_col4();
    ");
    echo "  ✓ trigger_detalle_delete_col4 creado\n";
    
    // TRIGGER 4: BEFORE UPDATE presupuesto_items - Mantener fórmula de saldo_disponible
    echo "  [4/4] Creando trigger para recalcular saldo_disponible...\n";
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
        CREATE TRIGGER trigger_col4_recalcula_saldo
        BEFORE UPDATE ON presupuesto_items
        FOR EACH ROW
        WHEN (OLD.col4 IS DISTINCT FROM NEW.col4)
        EXECUTE FUNCTION fn_trigger_col4_recalcula_saldo();
    ");
    echo "  ✓ trigger_col4_recalcula_saldo creado\n\n";
    
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ Solución Implementada                                 ║\n";
    echo "║                                                            ║\n";
    echo "║  Cambios:                                                 ║\n";
    echo "║  - col4 ahora = SUM(monto) de detalle_certificados      ║\n";
    echo "║  - INSERT: Recalcula col4 con SUM                       ║\n";
    echo "║  - DELETE: Recalcula col4 con SUM (items restantes)     ║\n";
    echo "║  - Al borrar certificado: col4 se reduce correctamente  ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
