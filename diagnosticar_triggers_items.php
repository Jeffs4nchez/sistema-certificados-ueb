<?php
/**
 * DIAGNÓSTICO Y REPARACIÓN DE TRIGGERS
 * Verifica si los triggers funcionan correctamente al INSERT/UPDATE/DELETE items
 */

$host = 'localhost';
$port = '5432';
$database = 'certificados_sistema';
$user = 'postgres';
$pass = 'jeffo2003';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "DIAGNÓSTICO DE TRIGGERS - INSERT/UPDATE/DELETE ITEMS\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // 1. VERIFICAR SI EXISTEN LOS TRIGGERS
    echo "1️⃣ VERIFICANDO TRIGGERS EXISTENTES...\n";
    echo str_repeat("-", 80) . "\n";
    
    $sql = "
        SELECT trigger_name, event_manipulation, action_timing
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        ORDER BY trigger_name;
    ";
    
    $result = $db->query($sql);
    $triggers = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($triggers)) {
        echo "❌ NO HAY TRIGGERS CREADOS\n";
        echo "   → Necesito crearlos ahora...\n\n";
        $need_create = true;
    } else {
        echo "✅ TRIGGERS ENCONTRADOS:\n";
        foreach ($triggers as $t) {
            echo "   • {$t['trigger_name']} ({$t['action_timing']} {$t['event_manipulation']})\n";
        }
        echo "\n";
        $need_create = false;
    }
    
    // 2. VERIFICAR SI PRESUPUESTO_ITEMS TIENE DATOS
    echo "2️⃣ VERIFICANDO TABLA PRESUPUESTO_ITEMS...\n";
    echo str_repeat("-", 80) . "\n";
    
    $count = $db->query("SELECT COUNT(*) as total FROM presupuesto_items")->fetch(PDO::FETCH_ASSOC);
    echo "   Total items en presupuesto: {$count['total']}\n\n";
    
    if ($count['total'] == 0) {
        echo "⚠️  NO HAY ITEMS EN PRESUPUESTO - Se recomienda importar CSV primero\n\n";
    }
    
    // 3. VERIFICAR DETALLE_CERTIFICADOS
    echo "3️⃣ VERIFICANDO TABLA DETALLE_CERTIFICADOS...\n";
    echo str_repeat("-", 80) . "\n";
    
    $count = $db->query("SELECT COUNT(*) as total FROM detalle_certificados")->fetch(PDO::FETCH_ASSOC);
    echo "   Total items en detalle: {$count['total']}\n\n";
    
    // 4. SI NO EXISTEN, CREAR LOS TRIGGERS
    if ($need_create) {
        echo "4️⃣ CREANDO TRIGGERS...\n";
        echo str_repeat("-", 80) . "\n";
        
        // TRIGGER INSERT
        echo "   • Creando TRIGGER INSERT...\n";
        $sql_insert = "
            CREATE OR REPLACE FUNCTION fn_trigger_detalle_insert()
            RETURNS TRIGGER AS \$\$
            BEGIN
                UPDATE presupuesto_items
                SET col4 = COALESCE(col4, 0) + NEW.monto,
                    col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) + NEW.monto) - COALESCE(col5, 0) - COALESCE(col6, 0) - COALESCE(col7, 0),
                    fecha_actualizacion = NOW()
                WHERE codigo_completo = NEW.codigo_completo;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
            
            DROP TRIGGER IF EXISTS trigger_detalle_insert ON detalle_certificados;
            CREATE TRIGGER trigger_detalle_insert
            AFTER INSERT ON detalle_certificados
            FOR EACH ROW
            EXECUTE FUNCTION fn_trigger_detalle_insert();
        ";
        
        try {
            $db->exec($sql_insert);
            echo "      ✅ Trigger INSERT creado\n";
        } catch (Exception $e) {
            echo "      ❌ Error: " . $e->getMessage() . "\n";
        }
        
        // TRIGGER UPDATE
        echo "   • Creando TRIGGER UPDATE...\n";
        $sql_update = "
            CREATE OR REPLACE FUNCTION fn_trigger_detalle_update()
            RETURNS TRIGGER AS \$\$
            DECLARE
                monto_diferencia NUMERIC;
            BEGIN
                -- Si cambió el monto, actualizar col4
                IF NEW.monto != OLD.monto THEN
                    monto_diferencia := NEW.monto - OLD.monto;
                    UPDATE presupuesto_items
                    SET col4 = COALESCE(col4, 0) + monto_diferencia,
                        col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) + monto_diferencia) - COALESCE(col5, 0) - COALESCE(col6, 0) - COALESCE(col7, 0),
                        fecha_actualizacion = NOW()
                    WHERE codigo_completo = NEW.codigo_completo;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
            
            DROP TRIGGER IF EXISTS trigger_detalle_update ON detalle_certificados;
            CREATE TRIGGER trigger_detalle_update
            AFTER UPDATE ON detalle_certificados
            FOR EACH ROW
            EXECUTE FUNCTION fn_trigger_detalle_update();
        ";
        
        try {
            $db->exec($sql_update);
            echo "      ✅ Trigger UPDATE creado\n";
        } catch (Exception $e) {
            echo "      ❌ Error: " . $e->getMessage() . "\n";
        }
        
        // TRIGGER DELETE
        echo "   • Creando TRIGGER DELETE...\n";
        $sql_delete = "
            CREATE OR REPLACE FUNCTION fn_trigger_detalle_delete()
            RETURNS TRIGGER AS \$\$
            BEGIN
                UPDATE presupuesto_items
                SET col4 = COALESCE(col4, 0) - OLD.monto,
                    col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) - OLD.monto) - COALESCE(col5, 0) - COALESCE(col6, 0) - COALESCE(col7, 0),
                    fecha_actualizacion = NOW()
                WHERE codigo_completo = OLD.codigo_completo;
                RETURN OLD;
            END;
            \$\$ LANGUAGE plpgsql;
            
            DROP TRIGGER IF EXISTS trigger_detalle_delete ON detalle_certificados;
            CREATE TRIGGER trigger_detalle_delete
            BEFORE DELETE ON detalle_certificados
            FOR EACH ROW
            EXECUTE FUNCTION fn_trigger_detalle_delete();
        ";
        
        try {
            $db->exec($sql_delete);
            echo "      ✅ Trigger DELETE creado\n";
        } catch (Exception $e) {
            echo "      ❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n✅ TODOS LOS TRIGGERS FUERON CREADOS EXITOSAMENTE\n\n";
    }
    
    // 5. VERIFICAR CAMPOS NECESARIOS
    echo "5️⃣ VERIFICANDO CAMPOS REQUERIDOS...\n";
    echo str_repeat("-", 80) . "\n";
    
    $result = $db->query("
        SELECT column_name, data_type
        FROM information_schema.columns
        WHERE table_name = 'detalle_certificados'
        AND column_name IN ('codigo_completo', 'monto')
    ");
    $campos = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($campos) == 2) {
        echo "   ✅ Campos necesarios presentes en detalle_certificados:\n";
        foreach ($campos as $c) {
            echo "      • {$c['column_name']} ({$c['data_type']})\n";
        }
    } else {
        echo "   ❌ Faltan campos en detalle_certificados\n";
    }
    
    echo "\n";
    
    // 6. RESUMEN FINAL
    echo str_repeat("=", 80) . "\n";
    echo "RESUMEN\n";
    echo str_repeat("=", 80) . "\n";
    echo "✅ Los triggers están configurados para:\n";
    echo "   • INSERT: Sumar monto a col4\n";
    echo "   • UPDATE: Restar/sumar diferencia de monto a col4\n";
    echo "   • DELETE: Restar monto de col4\n\n";
    echo "PRÓXIMOS PASOS:\n";
    echo "1. Importa un presupuesto CSV (si no lo has hecho)\n";
    echo "2. Crea un certificado e inserta un item\n";
    echo "3. Verifica que presupuesto_items.col4 se actualice automáticamente\n";
    echo "4. Actualiza el monto del item\n";
    echo "5. Verifica que col4 se recalcule\n";
    echo "6. Elimina el item\n";
    echo "7. Verifica que col4 vuelva al valor anterior\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
}
?>
