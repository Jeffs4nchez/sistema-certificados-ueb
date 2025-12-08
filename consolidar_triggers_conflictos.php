<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== CONSOLIDANDO TRIGGERS PARA EVITAR CONFLICTOS ===\n";

// Eliminar trigger_actualiza_total_pendiente
echo "\n📌 Paso 1: Eliminar trigger_actualiza_total_pendiente...\n";

$eliminar = [
    "DROP TRIGGER IF EXISTS trigger_actualiza_total_pendiente ON detalle_certificados CASCADE;",
    "DROP FUNCTION IF EXISTS fn_trigger_actualiza_total_pendiente() CASCADE;"
];

foreach ($eliminar as $sql) {
    try {
        $db->exec($sql);
        echo "✅ " . substr($sql, 0, 50) . "\n";
    } catch (Exception $e) {
        echo "⚠️  " . $e->getMessage() . "\n";
    }
}

// Crear función única que maneja TODO
echo "\n📌 Paso 2: Crear función consolidada para actualizar total_pendiente...\n";

$funcionConsolidada = "
CREATE OR REPLACE FUNCTION fn_actualiza_total_pendiente()
RETURNS TRIGGER AS \$\$
DECLARE
    v_suma_pendiente NUMERIC;
BEGIN
    -- Determinar el certificado_id según el evento
    DECLARE 
        v_cert_id INTEGER;
    BEGIN
        IF TG_OP = 'DELETE' THEN
            v_cert_id := OLD.certificado_id;
        ELSE
            v_cert_id := NEW.certificado_id;
        END IF;
        
        -- Calcular suma de cantidad_pendiente
        SELECT COALESCE(SUM(cantidad_pendiente), 0)
        INTO v_suma_pendiente
        FROM detalle_certificados
        WHERE certificado_id = v_cert_id;
        
        -- Actualizar total_pendiente en certificados
        UPDATE certificados
        SET total_pendiente = v_suma_pendiente
        WHERE id = v_cert_id;
    END;
    
    RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
END;
\$\$ LANGUAGE plpgsql;
";

try {
    $db->exec($funcionConsolidada);
    echo "✅ fn_actualiza_total_pendiente()\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Crear UN SOLO trigger con BEFORE que maneje todo
echo "\n📌 Paso 3: Crear trigger BEFORE UPDATE para consolidar...\n";

$triggers_nuevos = [
    // BEFORE INSERT - ejecuta primero
    "CREATE TRIGGER trigger_actualiza_total_pendiente_insert
    BEFORE INSERT ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_actualiza_total_pendiente();",
    
    // BEFORE DELETE - ejecuta primero
    "CREATE TRIGGER trigger_actualiza_total_pendiente_delete
    BEFORE DELETE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_actualiza_total_pendiente();",
    
    // BEFORE UPDATE (con recalcula_pendiente) - ejecuta primero
    "CREATE TRIGGER trigger_actualiza_total_pendiente_update
    BEFORE UPDATE ON detalle_certificados
    FOR EACH ROW
    WHEN (OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion)
    EXECUTE FUNCTION fn_actualiza_total_pendiente();"
];

foreach ($triggers_nuevos as $sql) {
    try {
        $db->exec($sql);
        preg_match('/trigger_(\w+)/', $sql, $m);
        echo "✅ " . $m[1] . " (BEFORE)\n";
    } catch (Exception $e) {
        echo "⚠️  Error\n";
    }
}

// Verificar orden final
echo "\n📌 Paso 4: Verificar nuevo orden de ejecución...\n";

$triggers_finales = $db->query("
    SELECT 
        trigger_name, event_manipulation, action_timing
    FROM information_schema.triggers
    WHERE trigger_schema = 'public' AND event_object_table = 'detalle_certificados'
    ORDER BY action_timing DESC, event_manipulation
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nNUEVO ORDEN:\n";
foreach ($triggers_finales as $t) {
    printf("%-50s | %-10s | %-10s\n",
        $t['trigger_name'],
        $t['event_manipulation'],
        $t['action_timing']
    );
}

echo "\n✅ Consolidación completada.\n";

echo "\n📋 NUEVO FLUJO:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "▼ INSERT:\n";
echo "   1. BEFORE INSERT → fn_actualiza_total_pendiente()\n";
echo "   2. AFTER INSERT → trigger_insert_col4()\n";
echo "\n▼ UPDATE (con cambio de liquidacion):\n";
echo "   1. BEFORE UPDATE → trigger_recalcula_pendiente()\n";
echo "   2. BEFORE UPDATE → fn_actualiza_total_pendiente()\n";
echo "   3. AFTER UPDATE → trigger_update_col4_consolidado()\n";
echo "\n▼ DELETE:\n";
echo "   1. BEFORE DELETE → fn_actualiza_total_pendiente()\n";
echo "   2. AFTER DELETE → trigger_delete_col4()\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

?>
