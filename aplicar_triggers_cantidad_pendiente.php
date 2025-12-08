<?php
/**
 * Script para eliminar triggers antiguos y crear los nuevos basados en cantidad_pendiente
 */

$host = 'localhost';
$port = '5432';
$user = 'postgres';
$pass = 'jeffo2003';
$database = 'certificados_sistema';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "🔧 Reemplazando triggers para usar cantidad_pendiente...\n";
    echo str_repeat("=", 120) . "\n\n";
    
    echo "🗑️ Eliminando triggers antiguos...\n";
    
    // Eliminar triggers y funciones antiguas directamente
    $dropStatements = [
        "DROP TRIGGER IF EXISTS trigger_detalle_cantidad_pendiente ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_detalle_insert_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_detalle_update_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_detalle_delete_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_col4_recalcula_saldo ON presupuesto_items CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_detalle_cantidad_pendiente() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_detalle_insert_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_detalle_update_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_detalle_delete_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_col4_recalcula_saldo() CASCADE",
    ];
    
    foreach ($dropStatements as $stmt) {
        try {
            $pdo->exec($stmt);
            echo "  ✓ Eliminado\n";
        } catch (Exception $e) {
            // Ignorar si no existen
        }
    }
    
    echo "\n⚡ Creando nuevas funciones basadas en cantidad_pendiente...\n";
    
    // Función 1: Insertar item - suma cantidad_pendiente a col4
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_insert_col4()
    RETURNS TRIGGER AS \$function\$
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
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_insert_col4()\n";
    
    // Función 2: Actualizar - ajustar col4 por diferencia de cantidad_pendiente
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_update_col4()
    RETURNS TRIGGER AS \$function\$
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
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_update_col4()\n";
    
    // Función 3: Eliminar - restar cantidad_pendiente de col4
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_delete_col4()
    RETURNS TRIGGER AS \$function\$
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
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_delete_col4()\n";
    
    // Función 4: Recalcular saldo_disponible
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_recalcula_saldo()
    RETURNS TRIGGER AS \$function\$
    BEGIN
        NEW.saldo_disponible := COALESCE(NEW.col3, 0) - COALESCE(NEW.col4, 0);
        NEW.fecha_actualizacion := NOW();
        RETURN NEW;
    END;
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_recalcula_saldo()\n";
    
    echo "\n🔌 Creando triggers...\n";
    
    // Trigger 1: INSERT
    $pdo->exec("
    CREATE TRIGGER trigger_insert_col4
    AFTER INSERT ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_insert_col4();
    ");
    echo "  ✓ trigger_insert_col4\n";
    
    // Trigger 2: UPDATE
    $pdo->exec("
    CREATE TRIGGER trigger_update_col4
    AFTER UPDATE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_update_col4();
    ");
    echo "  ✓ trigger_update_col4\n";
    
    // Trigger 3: DELETE
    $pdo->exec("
    CREATE TRIGGER trigger_delete_col4
    AFTER DELETE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_delete_col4();
    ");
    echo "  ✓ trigger_delete_col4\n";
    
    // Trigger 4: Saldo
    $pdo->exec("
    CREATE TRIGGER trigger_recalcula_saldo
    BEFORE UPDATE ON presupuesto_items
    FOR EACH ROW
    WHEN (OLD.col4 IS DISTINCT FROM NEW.col4 OR OLD.col3 IS DISTINCT FROM NEW.col3)
    EXECUTE FUNCTION fn_trigger_recalcula_saldo();
    ");
    echo "  ✓ trigger_recalcula_saldo\n";
    
    // Verificar triggers
    echo "\n" . str_repeat("=", 120) . "\n";
    echo "✅ VERIFICACIÓN DE NUEVOS TRIGGERS\n";
    echo str_repeat("=", 120) . "\n\n";
    
    $sql = "SELECT 
                trigger_name,
                event_object_table,
                event_manipulation,
                action_timing
            FROM information_schema.triggers
            WHERE trigger_schema = 'public' 
            ORDER BY event_object_table, trigger_name";
    
    $stmt = $pdo->query($sql);
    $triggers = $stmt->fetchAll();
    
    $currentTable = null;
    $count = 0;
    foreach ($triggers as $trigger) {
        if ($currentTable !== $trigger['event_object_table']) {
            $currentTable = $trigger['event_object_table'];
            echo "\n🔴 TABLA: {$currentTable}\n";
            echo str_repeat("-", 120) . "\n";
        }
        
        echo sprintf(
            "  ✓ %-40s | %s | %s\n",
            $trigger['trigger_name'],
            $trigger['action_timing'],
            $trigger['event_manipulation']
        );
        $count++;
    }
    
    echo "\n" . str_repeat("=", 120) . "\n";
    echo "✅ Total de triggers activos: $count\n";
    echo "✅ Triggers ahora usan cantidad_pendiente en lugar de monto\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
