<?php
/**
 * Script para aplicar los triggers optimizados a la BD
 * Ejecuta el archivo SQL completo sin dividir por ;
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
    
    echo "🔧 Eliminando triggers antiguos...\n";
    
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
            echo "  ✓ " . str_replace("DROP ", "", substr($stmt, 0, 60)) . "\n";
        } catch (Exception $e) {
            // Ignorar errores de DROP si no existen
        }
    }
    
    echo "\n🔨 Creando funciones de triggers...\n";
    
    // Función 1: cantidad_pendiente
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_detalle_cantidad_pendiente()
    RETURNS TRIGGER AS \$function\$
    BEGIN
        NEW.cantidad_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
        RETURN NEW;
    END;
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_detalle_cantidad_pendiente()\n";
    
    // Función 2: insert_col4
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_detalle_insert_col4()
    RETURNS TRIGGER AS \$function\$
    DECLARE
        presupuesto_id INTEGER;
    BEGIN
        SELECT id INTO presupuesto_id
        FROM presupuesto_items
        WHERE codigo_completo = NEW.codigo_completo
        LIMIT 1;
        
        IF presupuesto_id IS NOT NULL THEN
            UPDATE presupuesto_items
            SET 
                col4 = COALESCE(col4, 0) + NEW.monto,
                fecha_actualizacion = NOW()
            WHERE id = presupuesto_id;
        END IF;
        
        RETURN NEW;
    END;
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_detalle_insert_col4()\n";
    
    // Función 3: update_col4
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_detalle_update_col4()
    RETURNS TRIGGER AS \$function\$
    DECLARE
        presupuesto_id INTEGER;
        monto_diferencia NUMERIC;
    BEGIN
        IF OLD.monto IS DISTINCT FROM NEW.monto THEN
            monto_diferencia := NEW.monto - OLD.monto;
            
            SELECT id INTO presupuesto_id
            FROM presupuesto_items
            WHERE codigo_completo = NEW.codigo_completo
            LIMIT 1;
            
            IF presupuesto_id IS NOT NULL THEN
                UPDATE presupuesto_items
                SET 
                    col4 = COALESCE(col4, 0) + monto_diferencia,
                    fecha_actualizacion = NOW()
                WHERE id = presupuesto_id;
            END IF;
        END IF;
        
        RETURN NEW;
    END;
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_detalle_update_col4()\n";
    
    // Función 4: delete_col4
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_detalle_delete_col4()
    RETURNS TRIGGER AS \$function\$
    DECLARE
        presupuesto_id INTEGER;
    BEGIN
        SELECT id INTO presupuesto_id
        FROM presupuesto_items
        WHERE codigo_completo = OLD.codigo_completo
        LIMIT 1;
        
        IF presupuesto_id IS NOT NULL THEN
            UPDATE presupuesto_items
            SET 
                col4 = COALESCE(col4, 0) - OLD.monto,
                fecha_actualizacion = NOW()
            WHERE id = presupuesto_id;
        END IF;
        
        RETURN OLD;
    END;
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_detalle_delete_col4()\n";
    
    // Función 5: recalcula_saldo
    $pdo->exec("
    CREATE OR REPLACE FUNCTION fn_trigger_col4_recalcula_saldo()
    RETURNS TRIGGER AS \$function\$
    BEGIN
        NEW.saldo_disponible := COALESCE(NEW.col3, 0) - COALESCE(NEW.col4, 0);
        NEW.fecha_actualizacion := NOW();
        RETURN NEW;
    END;
    \$function\$ LANGUAGE plpgsql;
    ");
    echo "  ✓ fn_trigger_col4_recalcula_saldo()\n";
    
    echo "\n⚡ Creando triggers...\n";
    
    // Trigger 1
    $pdo->exec("
    CREATE TRIGGER trigger_detalle_cantidad_pendiente
    BEFORE INSERT OR UPDATE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_detalle_cantidad_pendiente();
    ");
    echo "  ✓ trigger_detalle_cantidad_pendiente\n";
    
    // Trigger 2
    $pdo->exec("
    CREATE TRIGGER trigger_detalle_insert_col4
    AFTER INSERT ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_detalle_insert_col4();
    ");
    echo "  ✓ trigger_detalle_insert_col4\n";
    
    // Trigger 3
    $pdo->exec("
    CREATE TRIGGER trigger_detalle_update_col4
    AFTER UPDATE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_detalle_update_col4();
    ");
    echo "  ✓ trigger_detalle_update_col4\n";
    
    // Trigger 4
    $pdo->exec("
    CREATE TRIGGER trigger_detalle_delete_col4
    AFTER DELETE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_detalle_delete_col4();
    ");
    echo "  ✓ trigger_detalle_delete_col4\n";
    
    // Trigger 5
    $pdo->exec("
    CREATE TRIGGER trigger_col4_recalcula_saldo
    BEFORE UPDATE ON presupuesto_items
    FOR EACH ROW
    WHEN (OLD.col4 IS DISTINCT FROM NEW.col4 OR OLD.col3 IS DISTINCT FROM NEW.col3)
    EXECUTE FUNCTION fn_trigger_col4_recalcula_saldo();
    ");
    echo "  ✓ trigger_col4_recalcula_saldo\n";
    
    // Verificar triggers
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "✅ VERIFICACIÓN DE TRIGGERS ACTIVOS\n";
    echo str_repeat("=", 100) . "\n\n";
    
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
            echo str_repeat("-", 100) . "\n";
        }
        
        echo sprintf(
            "  ✓ %-40s | %s | %s\n",
            $trigger['trigger_name'],
            $trigger['action_timing'],
            $trigger['event_manipulation']
        );
        $count++;
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "✅ Total de triggers activos: $count\n";
    echo "✅ Triggers optimizados aplicados correctamente!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
