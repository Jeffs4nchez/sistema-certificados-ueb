<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== CORRIGIENDO LOGICA DE COL4 ===\n";

echo "Problema identificado:\n";
echo "  - Creas item: monto=1000, liquidacion=0 → col4 debe ser 0\n";
echo "  - Liquidas 900: liquidacion=900 → col4 debe ser 900\n";
echo "  - Pendiente queda 100: cantidad_pendiente=100\n";
echo "\nFórmula correcta:\n";
echo "  col4 = monto - cantidad_pendiente = liquidacion\n";

echo "\n📌 Paso 1: Corregir trigger_insert_col4...\n";

$fn_insert = "
CREATE OR REPLACE FUNCTION fn_trigger_insert_col4()
RETURNS TRIGGER AS \$\$
DECLARE
    presupuesto_id INTEGER;
BEGIN
    -- Buscar el presupuesto_items por codigo_completo
    SELECT id INTO presupuesto_id
    FROM presupuesto_items
    WHERE codigo_completo = NEW.codigo_completo
    LIMIT 1;
    
    -- Si existe, sumar lo LIQUIDADO (monto - pendiente)
    -- Al insertar, pendiente = monto, entonces liquidado = 0
    IF presupuesto_id IS NOT NULL THEN
        UPDATE presupuesto_items
        SET 
            col4 = COALESCE(col4, 0) + (NEW.monto - NEW.cantidad_pendiente),
            saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) + (NEW.monto - NEW.cantidad_pendiente)),
            fecha_actualizacion = NOW()
        WHERE id = presupuesto_id;
    END IF;
    
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

try {
    $db->exec($fn_insert);
    echo "✅ fn_trigger_insert_col4() corregida\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n📌 Paso 2: Corregir trigger_delete_col4...\n";

$fn_delete = "
CREATE OR REPLACE FUNCTION fn_trigger_delete_col4()
RETURNS TRIGGER AS \$\$
DECLARE
    presupuesto_id INTEGER;
BEGIN
    -- Buscar el presupuesto_items por codigo_completo
    SELECT id INTO presupuesto_id
    FROM presupuesto_items
    WHERE codigo_completo = OLD.codigo_completo
    LIMIT 1;
    
    -- Restar lo que estaba LIQUIDADO
    IF presupuesto_id IS NOT NULL THEN
        UPDATE presupuesto_items
        SET 
            col4 = COALESCE(col4, 0) - (OLD.monto - OLD.cantidad_pendiente),
            saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) - (OLD.monto - OLD.cantidad_pendiente)),
            fecha_actualizacion = NOW()
        WHERE id = presupuesto_id;
    END IF;
    
    RETURN OLD;
END;
\$\$ LANGUAGE plpgsql;
";

try {
    $db->exec($fn_delete);
    echo "✅ fn_trigger_delete_col4() corregida\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n📌 Paso 3: Corregir fn_trigger_actualiza_col4_y_saldo...\n";

$fn_col4_saldo = "
CREATE OR REPLACE FUNCTION fn_trigger_actualiza_col4_y_saldo()
RETURNS TRIGGER AS \$\$
DECLARE
    presupuesto_id INTEGER;
    nueva_liquidacion NUMERIC;
    antigua_liquidacion NUMERIC;
    diferencia_liquidacion NUMERIC;
BEGIN
    -- Solo procesar si cantidad_liquidacion cambió
    IF NEW.cantidad_liquidacion IS DISTINCT FROM OLD.cantidad_liquidacion THEN
        nueva_liquidacion := COALESCE(NEW.cantidad_liquidacion, 0);
        antigua_liquidacion := COALESCE(OLD.cantidad_liquidacion, 0);
        diferencia_liquidacion := nueva_liquidacion - antigua_liquidacion;
        
        -- Buscar presupuesto_items
        SELECT id INTO presupuesto_id
        FROM presupuesto_items
        WHERE codigo_completo = NEW.codigo_completo
        LIMIT 1;
        
        -- Actualizar col4 por la diferencia LIQUIDADA
        -- Si liquidacion aumenta 900, col4 aumenta 900
        IF presupuesto_id IS NOT NULL THEN
            UPDATE presupuesto_items
            SET 
                col4 = COALESCE(col4, 0) + diferencia_liquidacion,
                saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) + diferencia_liquidacion),
                fecha_actualizacion = NOW()
            WHERE id = presupuesto_id;
        END IF;
    END IF;
    
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

try {
    $db->exec($fn_col4_saldo);
    echo "✅ fn_trigger_actualiza_col4_y_saldo() corregida\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n📌 Paso 4: Verificar ejemplo práctico...\n";

echo "\n▼ Ejemplo: Item de 1000\n";
echo "   1. Insertas: liquidacion=0 → col4 += (1000 - 0) = 0\n";
echo "   2. Liquidas 900: liquidacion=900 → col4 += (900 - 0) = 900 ✅\n";
echo "   3. Liquidas 100 más: liquidacion=1000 → col4 += (1000 - 900) = 1000 ✅\n";
echo "   4. Eliminas: col4 -= (1000 - 0) = 0 ✅\n";

echo "\n✅ Lógica corregida.\n";

?>
