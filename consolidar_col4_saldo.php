<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== CONSOLIDANDO UPDATE DE PRESUPUESTO ===\n";

// 1. Ver los triggers en presupuesto_items
echo "\n📌 Triggers en presupuesto_items:\n";

$triggers_presup = $db->query("
    SELECT trigger_name, event_manipulation, action_timing
    FROM information_schema.triggers
    WHERE trigger_schema = 'public' AND event_object_table = 'presupuesto_items'
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($triggers_presup as $t) {
    printf("  - %s (%s %s)\n", $t['trigger_name'], $t['action_timing'], $t['event_manipulation']);
}

// 2. Eliminar el trigger_recalcula_saldo separado
echo "\n📌 Paso 1: Eliminar trigger_recalcula_saldo (será consolidado)...\n";

try {
    $db->exec("DROP TRIGGER IF EXISTS trigger_recalcula_saldo ON presupuesto_items;");
    echo "✅ Trigger eliminado\n";
} catch (Exception $e) {
    echo "⚠️  Error: " . $e->getMessage() . "\n";
}

// 3. Crear función consolidada que hace TODO en una sola operación
echo "\n📌 Paso 2: Crear función consolidada para actualizar col4 Y saldo...\n";

$funcionConsolidada = "
CREATE OR REPLACE FUNCTION fn_trigger_actualiza_col4_y_saldo()
RETURNS TRIGGER AS \$\$
DECLARE
    presupuesto_id INTEGER;
    nueva_pendiente NUMERIC;
    antigua_pendiente NUMERIC;
    diferencia NUMERIC;
BEGIN
    -- Solo procesar si cantidad_liquidacion cambió
    IF NEW.cantidad_liquidacion IS DISTINCT FROM OLD.cantidad_liquidacion THEN
        -- Calcular diferencia en cantidad_pendiente
        antigua_pendiente := NEW.monto - COALESCE(OLD.cantidad_liquidacion, 0);
        nueva_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
        diferencia := nueva_pendiente - antigua_pendiente;
        
        -- Buscar presupuesto_items
        SELECT id INTO presupuesto_id
        FROM presupuesto_items
        WHERE codigo_completo = NEW.codigo_completo
        LIMIT 1;
        
        -- Actualizar col4 Y saldo_disponible EN UNA SOLA OPERACIÓN
        IF presupuesto_id IS NOT NULL THEN
            UPDATE presupuesto_items
            SET 
                col4 = COALESCE(col4, 0) - diferencia,
                saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) - diferencia),
                fecha_actualizacion = NOW()
            WHERE id = presupuesto_id;
        END IF;
    END IF;
    
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

try {
    $db->exec($funcionConsolidada);
    echo "✅ fn_trigger_actualiza_col4_y_saldo()\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 4. Reemplazar el trigger anterior
echo "\n📌 Paso 3: Reemplazar trigger_update_col4_consolidado...\n";

try {
    $db->exec("DROP TRIGGER IF EXISTS trigger_update_col4_consolidado ON detalle_certificados;");
    $db->exec("DROP FUNCTION IF EXISTS fn_trigger_sincroniza_col4();");
    echo "✅ Trigger anterior eliminado\n";
    
    $trigger_nuevo = "
    CREATE TRIGGER trigger_update_col4_consolidado
    AFTER UPDATE ON detalle_certificados
    FOR EACH ROW
    WHEN (OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion)
    EXECUTE FUNCTION fn_trigger_actualiza_col4_y_saldo();
    ";
    
    $db->exec($trigger_nuevo);
    echo "✅ Nuevo trigger creado (AFTER UPDATE)\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 5. Verificar estado final
echo "\n📌 Paso 4: Estado final de triggers...\n";

$all_triggers = $db->query("
    SELECT 
        trigger_name, 
        event_object_table,
        event_manipulation, 
        action_timing
    FROM information_schema.triggers
    WHERE trigger_schema = 'public'
    ORDER BY event_object_table, action_timing DESC, event_manipulation
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nTRIGGERS ACTIVOS:\n";
echo str_repeat("-", 100) . "\n";

$prev_tabla = '';
foreach ($all_triggers as $t) {
    if ($prev_tabla != $t['event_object_table']) {
        echo "\n" . strtoupper($t['event_object_table']) . ":\n";
        $prev_tabla = $t['event_object_table'];
    }
    printf("  %-45s | %-10s | %-10s\n",
        $t['trigger_name'],
        $t['action_timing'],
        $t['event_manipulation']
    );
}

echo "\n✅ Consolidación completada.\n";

echo "\n📋 FLUJO FINAL SIMPLIFICADO:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "▼ UPDATE (cambio de liquidacion):\n";
echo "   1. BEFORE UPDATE → trigger_recalcula_pendiente\n";
echo "       └─ Recalcula: cantidad_pendiente = monto - liquidacion\n";
echo "   2. BEFORE UPDATE → trigger_actualiza_total_pendiente_update\n";
echo "       └─ Recalcula: total_pendiente en certificados\n";
echo "   3. AFTER UPDATE → trigger_update_col4_consolidado\n";
echo "       └─ Actualiza col4 Y saldo_disponible EN UNA SOLA OPERACION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

?>
