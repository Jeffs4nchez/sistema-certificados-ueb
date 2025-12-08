<?php
/**
 * Eliminar triggers conflictivos y crear la lógica correcta
 * 
 * Col4 debe ser: SUM de cantidad_liquidacion de todos los items con ese codigo_completo
 * NO: SUM de cantidad_pendiente
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "CORREGIR LÓGICA: Col4 = SUM(cantidad_liquidacion)\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Eliminar triggers conflictivos
    echo "1️⃣  ELIMINAR TRIGGERS CONFLICTIVOS\n";
    echo str_repeat("-", 80) . "\n";
    
    $db->exec("DROP TRIGGER IF EXISTS trg_col4_after_insert ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trg_col4_after_update ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trg_col4_after_delete ON detalle_certificados CASCADE");
    $db->exec("DROP FUNCTION IF EXISTS fn_actualizar_col4_presupuesto() CASCADE");
    
    echo "✅ Triggers eliminados\n\n";
    
    // Crear nueva función que calcule correctamente
    echo "2️⃣  CREAR NUEVA FUNCIÓN\n";
    echo str_repeat("-", 80) . "\n";
    
    $create_function = "
        DROP FUNCTION IF EXISTS fn_actualizar_col4_por_liquidacion() CASCADE;
        
        CREATE FUNCTION fn_actualizar_col4_por_liquidacion()
        RETURNS TRIGGER AS \$\$
        BEGIN
            -- Col4 = SUM de cantidad_liquidacion de todos los items con ese codigo_completo
            -- Recalcular col4 después de cambios en cantidad_liquidacion
            
            IF TG_OP IN ('UPDATE', 'DELETE') THEN
                UPDATE presupuesto_items p
                SET col4 = COALESCE((
                    SELECT SUM(COALESCE(d.cantidad_liquidacion, 0))
                    FROM detalle_certificados d
                    WHERE d.codigo_completo = p.codigo_completo
                ), 0),
                    fecha_actualizacion = NOW()
                WHERE p.codigo_completo = CASE 
                    WHEN TG_OP = 'DELETE' THEN OLD.codigo_completo
                    ELSE NEW.codigo_completo
                END;
            END IF;
            
            RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
        END;
        \$\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($create_function);
    echo "✅ Función creada: fn_actualizar_col4_por_liquidacion\n\n";
    
    // Crear trigger UPDATE
    echo "3️⃣  CREAR TRIGGER AFTER UPDATE\n";
    echo str_repeat("-", 80) . "\n";
    
    $trigger_update = "
        DROP TRIGGER IF EXISTS trg_col4_por_liquidacion_update ON detalle_certificados CASCADE;
        
        CREATE TRIGGER trg_col4_por_liquidacion_update
        AFTER UPDATE OF cantidad_liquidacion ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_por_liquidacion();
    ";
    
    $db->exec($trigger_update);
    echo "✅ Trigger creado: trg_col4_por_liquidacion_update\n\n";
    
    // Crear trigger DELETE
    echo "4️⃣  CREAR TRIGGER AFTER DELETE\n";
    echo str_repeat("-", 80) . "\n";
    
    $trigger_delete = "
        DROP TRIGGER IF EXISTS trg_col4_por_liquidacion_delete ON detalle_certificados CASCADE;
        
        CREATE TRIGGER trg_col4_por_liquidacion_delete
        AFTER DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_por_liquidacion();
    ";
    
    $db->exec($trigger_delete);
    echo "✅ Trigger creado: trg_col4_por_liquidacion_delete\n\n";
    
    // Verificar
    echo "5️⃣  VERIFICAR\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT trigger_name, event_manipulation
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        AND trigger_name LIKE 'trg_col4%'
        ORDER BY trigger_name
    ");
    
    $triggers = $stmt->fetchAll();
    foreach ($triggers as $trg) {
        echo "✅ {$trg['trigger_name']} (AFTER {$trg['event_manipulation']})\n";
    }
    
    echo "\n✅ LÓGICA CORREGIDA\n";
    echo "Col4 = SUM(cantidad_liquidacion) de todos los items con ese codigo_completo\n";
    
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
