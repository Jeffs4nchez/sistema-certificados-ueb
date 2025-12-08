<?php
/**
 * SOLUCIÓN CORRECTA: Col4 = SUM(cantidad_liquidacion) 
 * O EQUIVALENTE: Col4 = SUM(monto - cantidad_pendiente)
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "SOLUCIÓN CORRECTA: Col4 debe ser SUM(cantidad_liquidacion)\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Eliminar todos los triggers conflictivos
    echo "1️⃣  LIMPIAR TRIGGERS\n";
    echo str_repeat("-", 80) . "\n";
    
    $db->exec("DROP TRIGGER IF EXISTS trg_col4_por_liquidacion_insert ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trg_col4_por_liquidacion_update ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trg_col4_por_liquidacion_delete ON detalle_certificados CASCADE");
    $db->exec("DROP FUNCTION IF EXISTS fn_actualizar_col4_por_liquidacion() CASCADE");
    
    echo "✅ Triggers eliminados\n\n";
    
    // Crear nueva función correcta
    echo "2️⃣  CREAR FUNCIÓN CORRECTA\n";
    echo str_repeat("-", 80) . "\n";
    
    $create_function = "
        DROP FUNCTION IF EXISTS fn_col4_igual_liquidacion() CASCADE;
        
        CREATE FUNCTION fn_col4_igual_liquidacion()
        RETURNS TRIGGER AS \$\$
        BEGIN
            -- Col4 = SUM(cantidad_liquidacion) de todos los items con ese codigo_completo
            -- En INSERT, UPDATE o DELETE: recalcular col4
            
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
            
            RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
        END;
        \$\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($create_function);
    echo "✅ Función creada: fn_col4_igual_liquidacion\n\n";
    
    // Crear triggers
    echo "3️⃣  CREAR TRIGGERS (INSERT, UPDATE, DELETE)\n";
    echo str_repeat("-", 80) . "\n";
    
    $triggers = [
        "DROP TRIGGER IF EXISTS trg_col4_insert ON detalle_certificados CASCADE;
         CREATE TRIGGER trg_col4_insert
         AFTER INSERT ON detalle_certificados
         FOR EACH ROW
         EXECUTE FUNCTION fn_col4_igual_liquidacion();",
        
        "DROP TRIGGER IF EXISTS trg_col4_update ON detalle_certificados CASCADE;
         CREATE TRIGGER trg_col4_update
         AFTER UPDATE OF cantidad_liquidacion ON detalle_certificados
         FOR EACH ROW
         EXECUTE FUNCTION fn_col4_igual_liquidacion();",
        
        "DROP TRIGGER IF EXISTS trg_col4_delete ON detalle_certificados CASCADE;
         CREATE TRIGGER trg_col4_delete
         AFTER DELETE ON detalle_certificados
         FOR EACH ROW
         EXECUTE FUNCTION fn_col4_igual_liquidacion();"
    ];
    
    foreach ($triggers as $trigger_sql) {
        $db->exec($trigger_sql);
    }
    
    echo "✅ Triggers creados\n\n";
    
    // Verificar
    echo "4️⃣  VERIFICAR\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT trigger_name, event_manipulation
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        AND trigger_name LIKE 'trg_col4%'
        ORDER BY trigger_name
    ");
    
    $triggers_list = $stmt->fetchAll();
    foreach ($triggers_list as $trg) {
        echo "✅ {$trg['trigger_name']} (AFTER {$trg['event_manipulation']})\n";
    }
    
    echo "\n5️⃣  RECALCULAR COL4 PARA TODOS LOS ITEMS\n";
    echo str_repeat("-", 80) . "\n";
    
    // Recalcular col4 para todos los presupuestos
    $update_sql = "
        UPDATE presupuesto_items p
        SET col4 = COALESCE((
            SELECT SUM(COALESCE(d.cantidad_liquidacion, 0))
            FROM detalle_certificados d
            WHERE d.codigo_completo = p.codigo_completo
        ), 0)
        WHERE p.codigo_completo IN (
            SELECT DISTINCT codigo_completo 
            FROM detalle_certificados 
            WHERE codigo_completo IS NOT NULL
        )
    ";
    
    $affected = $db->exec($update_sql);
    echo "✅ Presupuestos recalculados: $affected\n\n";
    
    // Verificar resultados
    echo "6️⃣  VERIFICAR RESULTADOS\n";
    echo str_repeat("-", 80) . "\n";
    
    $verify = $db->query("
        SELECT 
            p.codigo_completo,
            p.col4 as col4_presupuesto,
            COALESCE(SUM(d.cantidad_liquidacion), 0) as suma_liquidaciones,
            COUNT(d.id) as cantidad_items
        FROM presupuesto_items p
        LEFT JOIN detalle_certificados d ON d.codigo_completo = p.codigo_completo
        WHERE p.codigo_completo IN (
            SELECT DISTINCT codigo_completo 
            FROM detalle_certificados 
            WHERE codigo_completo IS NOT NULL
        )
        GROUP BY p.codigo_completo, p.col4
    ");
    
    $results = $verify->fetchAll();
    
    $todos_ok = true;
    foreach ($results as $row) {
        $ok = ($row['col4_presupuesto'] == $row['suma_liquidaciones']) ? "✅" : "❌";
        echo "$ok {$row['codigo_completo']}: Col4 = " . number_format($row['col4_presupuesto'], 0) . " (Liquidaciones: " . number_format($row['suma_liquidaciones'], 0) . ", Items: {$row['cantidad_items']})\n";
        
        if ($row['col4_presupuesto'] != $row['suma_liquidaciones']) {
            $todos_ok = false;
        }
    }
    
    echo "\n";
    if ($todos_ok) {
        echo "✅✅✅ TODOS LOS COL4 CORRECTOS ✅✅✅\n";
    } else {
        echo "❌ HAY COL4 INCORRECTOS\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
