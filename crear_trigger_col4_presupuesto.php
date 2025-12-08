<?php
/**
 * Crear trigger que reste col4 en presupuesto_items
 * basado en cantidad_pendiente de cada item específico
 * 
 * Fórmula: col4 -= cantidad_pendiente (usando codigo_completo)
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "CREAR TRIGGER: Restar col4 en presupuesto por cantidad_pendiente\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // PASO 1: Crear función
    echo "1️⃣  CREAR FUNCIÓN\n";
    echo str_repeat("-", 80) . "\n";
    
    $create_function = "
        DROP FUNCTION IF EXISTS fn_actualizar_col4_presupuesto() CASCADE;
        
        CREATE FUNCTION fn_actualizar_col4_presupuesto()
        RETURNS TRIGGER AS \$\$
        DECLARE
            v_diferencia_pendiente NUMERIC;
        BEGIN
            -- En UPDATE: restar la DIFERENCIA de cantidad_pendiente
            -- En INSERT: hacer nada (el otro trigger trg_item_insert se encarga de col4 += monto)
            -- En DELETE: sumar la cantidad_pendiente (reverse)
            
            IF TG_OP = 'UPDATE' THEN
                -- Calcular diferencia: nueva_pendiente - antigua_pendiente
                v_diferencia_pendiente := NEW.cantidad_pendiente - OLD.cantidad_pendiente;
                
                -- Restar esa diferencia de col4
                IF v_diferencia_pendiente != 0 AND NEW.codigo_completo IS NOT NULL THEN
                    UPDATE presupuesto_items
                    SET col4 = COALESCE(col4, 0) - v_diferencia_pendiente,
                        fecha_actualizacion = NOW()
                    WHERE codigo_completo = NEW.codigo_completo;
                END IF;
                
            ELSIF TG_OP = 'DELETE' THEN
                -- Al eliminar, devolver la cantidad_pendiente a col4
                IF OLD.codigo_completo IS NOT NULL THEN
                    UPDATE presupuesto_items
                    SET col4 = COALESCE(col4, 0) + OLD.cantidad_pendiente,
                        fecha_actualizacion = NOW()
                    WHERE codigo_completo = OLD.codigo_completo;
                END IF;
            END IF;
            
            RETURN NEW;
        END;
        \$\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($create_function);
    echo "✅ Función creada: fn_actualizar_col4_presupuesto\n\n";
    
    // PASO 2: Crear trigger AFTER INSERT
    echo "2️⃣  CREAR TRIGGER AFTER INSERT\n";
    echo str_repeat("-", 80) . "\n";
    
    $trigger_insert = "
        DROP TRIGGER IF EXISTS trg_col4_after_insert ON detalle_certificados CASCADE;
        
        CREATE TRIGGER trg_col4_after_insert
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_presupuesto();
    ";
    
    $db->exec($trigger_insert);
    echo "✅ Trigger creado: trg_col4_after_insert (AFTER INSERT)\n\n";
    
    // PASO 3: Crear trigger AFTER UPDATE
    echo "3️⃣  CREAR TRIGGER AFTER UPDATE\n";
    echo str_repeat("-", 80) . "\n";
    
    $trigger_update = "
        DROP TRIGGER IF EXISTS trg_col4_after_update ON detalle_certificados CASCADE;
        
        CREATE TRIGGER trg_col4_after_update
        AFTER UPDATE OF cantidad_liquidacion, cantidad_pendiente ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_presupuesto();
    ";
    
    $db->exec($trigger_update);
    echo "✅ Trigger creado: trg_col4_after_update (AFTER UPDATE)\n\n";
    
    // PASO 4: Crear trigger AFTER DELETE
    echo "4️⃣  CREAR TRIGGER AFTER DELETE\n";
    echo str_repeat("-", 80) . "\n";
    
    $trigger_delete = "
        DROP TRIGGER IF EXISTS trg_col4_after_delete ON detalle_certificados CASCADE;
        
        CREATE TRIGGER trg_col4_after_delete
        AFTER DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_presupuesto();
    ";
    
    $db->exec($trigger_delete);
    echo "✅ Trigger creado: trg_col4_after_delete (AFTER DELETE)\n\n";
    
    // PASO 5: Verificar triggers
    echo "5️⃣  VERIFICAR TRIGGERS\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT trigger_name, event_manipulation, action_timing
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        AND trigger_name LIKE 'trg_col4%'
        ORDER BY trigger_name
    ");
    
    $triggers = $stmt->fetchAll();
    
    foreach ($triggers as $trg) {
        echo "✅ {$trg['trigger_name']} ({$trg['action_timing']} {$trg['event_manipulation']})\n";
    }
    
    echo "\n✅✅✅ TODOS LOS TRIGGERS CREADOS EXITOSAMENTE ✅✅✅\n";
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
