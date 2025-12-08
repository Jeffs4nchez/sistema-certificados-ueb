<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== RESTAURANDO TRIGGERS ELIMINADOS ===\n";

// Crear las funciones
$funciones = [
    // Función 1: INSERT - suma cantidad_pendiente a col4
    "CREATE OR REPLACE FUNCTION fn_trigger_insert_col4()
    RETURNS TRIGGER AS \$\$
    DECLARE
        presupuesto_id INTEGER;
    BEGIN
        -- Buscar el presupuesto_items por codigo_completo
        SELECT id INTO presupuesto_id
        FROM presupuesto_items
        WHERE codigo_completo = NEW.codigo_completo
        LIMIT 1;
        
        -- Si existe el presupuesto, actualizar col4 con cantidad_pendiente
        IF presupuesto_id IS NOT NULL THEN
            UPDATE presupuesto_items
            SET 
                col4 = COALESCE(col4, 0) + NEW.cantidad_pendiente,
                saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) + NEW.cantidad_pendiente),
                fecha_actualizacion = NOW()
            WHERE id = presupuesto_id;
        END IF;
        
        RETURN NEW;
    END;
    \$\$ LANGUAGE plpgsql;",
    
    // Función 2: UPDATE - ajustar col4 por diferencia
    "CREATE OR REPLACE FUNCTION fn_trigger_update_col4()
    RETURNS TRIGGER AS \$\$
    DECLARE
        presupuesto_id INTEGER;
        diferencia NUMERIC;
    BEGIN
        -- Calcular la diferencia en cantidad_pendiente
        diferencia := NEW.cantidad_pendiente - OLD.cantidad_pendiente;
        
        -- Si cantidad_pendiente cambió
        IF diferencia != 0 THEN
            -- Buscar el presupuesto_items por codigo_completo
            SELECT id INTO presupuesto_id
            FROM presupuesto_items
            WHERE codigo_completo = NEW.codigo_completo
            LIMIT 1;
            
            -- Si existe el presupuesto, ajustar col4 por la diferencia
            IF presupuesto_id IS NOT NULL THEN
                UPDATE presupuesto_items
                SET 
                    col4 = COALESCE(col4, 0) + diferencia,
                    saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) + diferencia),
                    fecha_actualizacion = NOW()
                WHERE id = presupuesto_id;
            END IF;
        END IF;
        
        RETURN NEW;
    END;
    \$\$ LANGUAGE plpgsql;",
    
    // Función 3: DELETE - resta cantidad_pendiente de col4
    "CREATE OR REPLACE FUNCTION fn_trigger_delete_col4()
    RETURNS TRIGGER AS \$\$
    DECLARE
        presupuesto_id INTEGER;
    BEGIN
        -- Buscar el presupuesto_items por codigo_completo
        SELECT id INTO presupuesto_id
        FROM presupuesto_items
        WHERE codigo_completo = OLD.codigo_completo
        LIMIT 1;
        
        -- Si existe el presupuesto, restar cantidad_pendiente de col4
        IF presupuesto_id IS NOT NULL THEN
            UPDATE presupuesto_items
            SET 
                col4 = COALESCE(col4, 0) - OLD.cantidad_pendiente,
                saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) - OLD.cantidad_pendiente),
                fecha_actualizacion = NOW()
            WHERE id = presupuesto_id;
        END IF;
        
        RETURN OLD;
    END;
    \$\$ LANGUAGE plpgsql;"
];

echo "📌 Paso 1: Crear funciones...\n";
foreach ($funciones as $sql) {
    try {
        $db->exec($sql);
        $match = [];
        preg_match('/FUNCTION (fn_\w+)/', $sql, $match);
        echo "✅ " . ($match[1] ?? 'Función') . "()\n";
    } catch (Exception $e) {
        echo "⚠️  Error: " . $e->getMessage() . "\n";
    }
}

// Crear los triggers
$triggers = [
    "DROP TRIGGER IF EXISTS trigger_insert_col4 ON detalle_certificados;",
    "CREATE TRIGGER trigger_insert_col4
    AFTER INSERT ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_insert_col4();",
    
    "DROP TRIGGER IF EXISTS trigger_update_col4 ON detalle_certificados;",
    "CREATE TRIGGER trigger_update_col4
    AFTER UPDATE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_update_col4();",
    
    "DROP TRIGGER IF EXISTS trigger_delete_col4 ON detalle_certificados;",
    "CREATE TRIGGER trigger_delete_col4
    AFTER DELETE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_delete_col4();"
];

echo "\n📌 Paso 2: Crear triggers...\n";
foreach ($triggers as $sql) {
    try {
        $db->exec($sql);
        $match = [];
        preg_match('/TRIGGER (\w+)|DROP/', $sql, $match);
        if (!empty($match[1])) {
            echo "✅ " . $match[1] . "\n";
        }
    } catch (Exception $e) {
        echo "⚠️  " . substr($sql, 0, 50) . "...\n";
    }
}

// Verificar
echo "\n📌 Paso 3: Verificar triggers activos...\n";
$activeTrigg = $db->query("
    SELECT trigger_name, event_manipulation
    FROM information_schema.triggers
    WHERE trigger_schema = 'public' AND event_object_table = 'detalle_certificados'
    ORDER BY trigger_name
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($activeTrigg as $t) {
    echo "✅ " . $t['trigger_name'] . " (" . $t['event_manipulation'] . ")\n";
}

echo "\n✅ Triggers restaurados.\n";
?>
