<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  Solución: Rastrear Aporte Individual a col4            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // PASO 1: Agregar columna para rastrear aporte a col4
    echo "📌 PASO 1: Agregar columna monto_en_col4 a detalle_certificados...\n";
    
    $db->exec("
        ALTER TABLE detalle_certificados 
        ADD COLUMN IF NOT EXISTS monto_en_col4 DECIMAL(14,2) DEFAULT 0
    ");
    
    echo "  ✓ Columna agregada\n\n";
    
    // PASO 2: Modificar trigger INSERT para guardar el monto_en_col4
    echo "📌 PASO 2: Actualizar trigger INSERT...\n";
    
    $db->exec("
        DROP TRIGGER IF EXISTS trigger_detalle_insert_col4 ON detalle_certificados CASCADE;
        DROP FUNCTION IF EXISTS fn_trigger_detalle_insert_col4() CASCADE;
    ");
    
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_insert_col4()
        RETURNS TRIGGER AS \$\$
        BEGIN
            -- Guardar el monto que estamos agregando a col4
            NEW.monto_en_col4 := NEW.monto;
            
            -- Actualizar col4 en presupuesto_items
            UPDATE presupuesto_items
            SET col4 = COALESCE(col4, 0) + NEW.monto,
                saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) + NEW.monto),
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
    
    echo "  ✓ Trigger INSERT actualizado\n\n";
    
    // PASO 3: Modificar trigger DELETE para usar monto_en_col4
    echo "📌 PASO 3: Actualizar trigger DELETE...\n";
    
    $db->exec("
        DROP TRIGGER IF EXISTS trigger_detalle_delete_col4 ON detalle_certificados CASCADE;
        DROP FUNCTION IF EXISTS fn_trigger_detalle_delete_col4() CASCADE;
    ");
    
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_delete_col4()
        RETURNS TRIGGER AS \$\$
        DECLARE
            monto_a_restar DECIMAL(14,2);
            col4_nuevo DECIMAL(14,2);
        BEGIN
            -- Usar monto_en_col4 para restar exactamente lo que aportó este certificado
            monto_a_restar := COALESCE(OLD.monto_en_col4, OLD.monto);
            
            -- Calcular nuevo col4
            col4_nuevo := COALESCE((
                SELECT col4 FROM presupuesto_items WHERE codigo_completo = OLD.codigo_completo
            ), 0) - monto_a_restar;
            
            -- Asegurar que col4 no sea negativo
            IF col4_nuevo < 0 THEN
                col4_nuevo := 0;
            END IF;
            
            -- Actualizar presupuesto_items
            UPDATE presupuesto_items
            SET col4 = col4_nuevo,
                saldo_disponible = COALESCE(col3, 0) - col4_nuevo,
                fecha_actualizacion = NOW()
            WHERE codigo_completo = OLD.codigo_completo;
            
            -- Loguear
            INSERT INTO trigger_logs (trigger_name, action, codigo_completo, monto_amount, col4_before, col4_after)
            VALUES ('trigger_detalle_delete_col4', 'DELETE', OLD.codigo_completo, monto_a_restar, 
                    COALESCE((SELECT col4 + monto_a_restar FROM presupuesto_items WHERE codigo_completo = OLD.codigo_completo LIMIT 1), monto_a_restar), 
                    col4_nuevo);
            
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
    
    echo "  ✓ Trigger DELETE actualizado\n\n";
    
    // PASO 4: Modificar trigger UPDATE para manejar cambios en cantidad_liquidacion
    echo "📌 PASO 4: Actualizar trigger UPDATE...\n";
    
    $db->exec("
        DROP TRIGGER IF EXISTS trigger_detalle_update_col4 ON detalle_certificados CASCADE;
        DROP FUNCTION IF EXISTS fn_trigger_detalle_update_col4() CASCADE;
    ");
    
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
                    saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) + diferencia),
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
    
    echo "  ✓ Trigger UPDATE actualizado\n\n";
    
    // PASO 5: Actualizar registros existentes con monto_en_col4
    echo "📌 PASO 5: Recalcular monto_en_col4 para registros existentes...\n";
    
    $db->exec("
        UPDATE detalle_certificados 
        SET monto_en_col4 = monto 
        WHERE monto_en_col4 = 0 AND monto > 0
    ");
    
    $result = $db->query("SELECT COUNT(*) as cnt FROM detalle_certificados WHERE monto_en_col4 > 0");
    $count = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "  ✓ {$count['cnt']} registros actualizados\n\n";
    
    // PASO 6: Recalcular col4 para todos los presupuestos
    echo "📌 PASO 6: Recalcular col4 correctamente para cada código presupuestario...\n";
    
    $db->exec("
        UPDATE presupuesto_items pi
        SET col4 = COALESCE((
            SELECT SUM(COALESCE(monto_en_col4, monto))
            FROM detalle_certificados dc
            WHERE dc.codigo_completo = pi.codigo_completo
        ), 0),
        saldo_disponible = COALESCE(col3, 0) - COALESCE((
            SELECT SUM(COALESCE(monto_en_col4, monto))
            FROM detalle_certificados dc
            WHERE dc.codigo_completo = pi.codigo_completo
        ), 0)
    ");
    
    echo "  ✓ col4 recalculado para todos los presupuestos\n\n";
    
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ Solución Implementada                                 ║\n";
    echo "║                                                            ║\n";
    echo "║  Cambios realizados:                                      ║\n";
    echo "║  - Agregada columna monto_en_col4 a detalle_certificados ║\n";
    echo "║  - Trigger INSERT guarda monto_en_col4                   ║\n";
    echo "║  - Trigger DELETE resta SOLO monto_en_col4               ║\n";
    echo "║  - Todos los valores de col4 recalculados                ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
