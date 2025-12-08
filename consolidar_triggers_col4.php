<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== CONSOLIDANDO TRIGGERS DE COL4 ===\n";

// Eliminar triggers conflictivos
echo "\n📌 Paso 1: Eliminar triggers duplicados...\n";

$eliminar = [
    "DROP TRIGGER IF EXISTS trigger_sincroniza_liquidacion ON detalle_certificados;",
    "DROP TRIGGER IF EXISTS trigger_update_col4 ON detalle_certificados;",
    "DROP FUNCTION IF EXISTS fn_trigger_sincroniza_liquidacion();",
    "DROP FUNCTION IF EXISTS fn_trigger_update_col4();"
];

foreach ($eliminar as $sql) {
    try {
        $db->exec($sql);
        echo "✅ " . substr($sql, 0, 50) . "\n";
    } catch (Exception $e) {
        echo "⚠️  Error\n";
    }
}

// Crear UNA función que maneje TODO el flujo de col4
echo "\n📌 Paso 2: Crear función única para sincronizar col4...\n";

$funcionUnica = "
CREATE OR REPLACE FUNCTION fn_trigger_sincroniza_col4()
RETURNS TRIGGER AS \$\$
DECLARE
    presupuesto_id INTEGER;
    nueva_pendiente NUMERIC;
    antigua_pendiente NUMERIC;
    diferencia NUMERIC;
BEGIN
    -- Calcular cantidad_pendiente antigua y nueva
    antigua_pendiente := NEW.monto - COALESCE(OLD.cantidad_liquidacion, 0);
    nueva_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
    diferencia := nueva_pendiente - antigua_pendiente;
    
    -- Buscar el presupuesto_items por codigo_completo
    SELECT id INTO presupuesto_id
    FROM presupuesto_items
    WHERE codigo_completo = NEW.codigo_completo
    LIMIT 1;
    
    -- Si existe presupuesto, ajustar col4
    -- col4 = col4 - diferencia (si pendiente disminuye, col4 aumenta; si aumenta, col4 disminuye)
    IF presupuesto_id IS NOT NULL THEN
        UPDATE presupuesto_items
        SET 
            col4 = COALESCE(col4, 0) - diferencia,
            saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) - diferencia),
            fecha_actualizacion = NOW()
        WHERE id = presupuesto_id;
    END IF;
    
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

try {
    $db->exec($funcionUnica);
    echo "✅ fn_trigger_sincroniza_col4()\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Crear trigger único para UPDATE
echo "\n📌 Paso 3: Crear trigger único para UPDATE...\n";

$triggerUpdate = "
DROP TRIGGER IF EXISTS trigger_update_col4_consolidado ON detalle_certificados;

CREATE TRIGGER trigger_update_col4_consolidado
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
WHEN (OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion)
EXECUTE FUNCTION fn_trigger_sincroniza_col4();
";

try {
    $db->exec($triggerUpdate);
    echo "✅ trigger_update_col4_consolidado (UPDATE)\n";
} catch (Exception $e) {
    echo "⚠️  Error: " . $e->getMessage() . "\n";
}

// Mantener los otros triggers para INSERT y DELETE
echo "\n📌 Paso 4: Mantener triggers para INSERT y DELETE...\n";

$verificar = $db->query("
    SELECT trigger_name, event_manipulation, event_object_table
    FROM information_schema.triggers
    WHERE trigger_schema = 'public'
    ORDER BY event_object_table, trigger_name
")->fetchAll(PDO::FETCH_ASSOC);

echo "\n📋 TRIGGERS FINALES:\n";
echo str_repeat("-", 100) . "\n";
foreach ($verificar as $t) {
    printf("%-35s | %-10s | %s\n", 
        $t['trigger_name'],
        $t['event_manipulation'],
        $t['event_object_table']
    );
}

echo "\n✅ Consolidación completada.\n";
echo "\nFlujo ahora:\n";
echo "   1. Creas item → trigger_insert_col4 suma cantidad_pendiente a col4\n";
echo "   2. Liquidass item → recalcula pendiente → trigger_update_col4_consolidado ajusta col4\n";
echo "   3. Borras item → trigger_delete_col4 resta cantidad_pendiente de col4\n";
?>
