<?php
/**
 * SOLUCIÓN CORRECTA: USAR BEFORE INSERT TRIGGER
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "SOLUCIÓN: USAR BEFORE INSERT TRIGGER\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Eliminar triggers viejos
    echo "1️⃣  LIMPIAR TRIGGERS VIEJOS\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP TRIGGER IF EXISTS trg_pendiente_insert ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trg_pendiente_update ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trg_pendiente_delete ON detalle_certificados CASCADE");
    echo "✅ Triggers eliminados\n\n";
    
    // 2. Ver si existe fn_trigger_detalle_cantidad_pendiente
    echo "2️⃣  VER FUNCIONES EXISTENTES\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT proname, pg_get_functiondef(oid)
        FROM pg_proc
        WHERE proname LIKE '%cantidad_pendiente%' OR proname LIKE '%restar%'
        ORDER BY proname
    ");
    
    $funcs = $stmt->fetchAll();
    foreach ($funcs as $func) {
        echo "  - {$func['proname']}\n";
    }
    echo "\n";
    
    // 3. Crear UNA función que hace todo: calcular cantidad_pendiente Y actualizar col4
    echo "3️⃣  CREAR FUNCIÓN ÚNICA QUE CALCULA Y ACTUALIZA\n";
    echo str_repeat("-", 100) . "\n";
    
    // Primero, eliminar función vieja si existe
    $db->exec("DROP FUNCTION IF EXISTS fn_detalle_insert_update() CASCADE");
    
    $sql = "
        CREATE FUNCTION fn_detalle_insert_update()
        RETURNS TRIGGER AS \$func\$
        BEGIN
            -- 1. CALCULAR cantidad_pendiente si no está informado
            IF NEW.cantidad_pendiente IS NULL OR NEW.cantidad_pendiente = 0 THEN
                NEW.cantidad_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
            END IF;
            
            -- 2. RESTAR cantidad_pendiente DE col4
            IF NEW.codigo_completo IS NOT NULL AND NEW.cantidad_pendiente > 0 THEN
                UPDATE presupuesto_items
                SET col4 = COALESCE(col4, 0) - NEW.cantidad_pendiente
                WHERE codigo_completo = NEW.codigo_completo;
            END IF;
            
            RETURN NEW;
        END;
        \$func\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($sql);
    echo "✅ Función creada: fn_detalle_insert_update()\n\n";
    
    // 4. Crear BEFORE INSERT trigger
    echo "4️⃣  CREAR BEFORE INSERT TRIGGER\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trg_detalle_before_insert
        BEFORE INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_detalle_insert_update()
    ");
    echo "✅ Trigger creado\n\n";
    
    // 5. Crear función para BEFORE UPDATE
    echo "5️⃣  CREAR FUNCIÓN PARA UPDATE\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP FUNCTION IF EXISTS fn_detalle_before_update() CASCADE");
    
    $sql = "
        CREATE FUNCTION fn_detalle_before_update()
        RETURNS TRIGGER AS \$func\$
        DECLARE
            v_diferencia NUMERIC;
        BEGIN
            -- 1. RECALCULAR cantidad_pendiente
            NEW.cantidad_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
            
            -- 2. ACTUALIZAR col4 CON LA DIFERENCIA
            IF NEW.codigo_completo IS NOT NULL THEN
                -- Diferencia = (pendiente_nueva) - (pendiente_vieja)
                v_diferencia := NEW.cantidad_pendiente - OLD.cantidad_pendiente;
                
                -- Restamos la diferencia
                IF v_diferencia != 0 THEN
                    UPDATE presupuesto_items
                    SET col4 = COALESCE(col4, 0) - v_diferencia
                    WHERE codigo_completo = NEW.codigo_completo;
                END IF;
            END IF;
            
            RETURN NEW;
        END;
        \$func\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($sql);
    echo "✅ Función para UPDATE creada\n\n";
    
    // 6. Crear BEFORE UPDATE trigger
    echo "6️⃣  CREAR BEFORE UPDATE TRIGGER\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trg_detalle_before_update
        BEFORE UPDATE OF cantidad_liquidacion, cantidad_pendiente, monto ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_detalle_before_update()
    ");
    echo "✅ Trigger creado\n\n";
    
    // 7. Crear función para DELETE
    echo "7️⃣  CREAR FUNCIÓN PARA DELETE\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP FUNCTION IF EXISTS fn_detalle_before_delete() CASCADE");
    
    $sql = "
        CREATE FUNCTION fn_detalle_before_delete()
        RETURNS TRIGGER AS \$func\$
        BEGIN
            -- En DELETE, sumamos la cantidad_pendiente de vuelta
            IF OLD.codigo_completo IS NOT NULL AND OLD.cantidad_pendiente > 0 THEN
                UPDATE presupuesto_items
                SET col4 = COALESCE(col4, 0) + OLD.cantidad_pendiente
                WHERE codigo_completo = OLD.codigo_completo;
            END IF;
            
            RETURN OLD;
        END;
        \$func\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($sql);
    echo "✅ Función para DELETE creada\n\n";
    
    // 8. Crear BEFORE DELETE trigger
    echo "8️⃣  CREAR BEFORE DELETE TRIGGER\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trg_detalle_before_delete
        BEFORE DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_detalle_before_delete()
    ");
    echo "✅ Trigger creado\n\n";
    
    // 9. PROBAR CON INSERT
    echo "9️⃣  PROBAR CON INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $codigo_test = 'TEST-BEFORE-' . date('YmdHis');
    
    $stmt = $db->prepare("
        INSERT INTO presupuesto_items 
        (codigo_completo, col4, col1, col2, col3, fecha_creacion)
        VALUES (?, ?, 1, 1, 1, NOW())
        RETURNING id, col4
    ");
    $stmt->execute([$codigo_test, 30000]);
    $presupuesto = $stmt->fetch();
    
    echo "Presupuesto: $codigo_test\n";
    echo "Col4 ANTES: " . number_format($presupuesto['col4'], 2) . "\n\n";
    
    // Crear certificado
    $stmt = $db->prepare("
        INSERT INTO certificados
        (numero_certificado, institucion, seccion_memorando, descripcion, fecha_elaboracion, monto_total, usuario_creacion, fecha_creacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        RETURNING id
    ");
    $stmt->execute(['TEST-' . uniqid(), 'TEST', 'TEST', 'Test Before', date('Y-m-d'), 7000, 'admin']);
    $cert_id = $stmt->fetchColumn();
    
    // Insertar item
    echo "Insertando item: Monto=7000, Liquidado=0\n";
    $stmt = $db->prepare("
        INSERT INTO detalle_certificados
        (certificado_id, monto, cantidad_liquidacion, codigo_completo)
        VALUES (?, ?, ?, ?)
        RETURNING id, cantidad_pendiente
    ");
    $stmt->execute([$cert_id, 7000, 0, $codigo_test]);
    $item = $stmt->fetch();
    
    echo "✅ Item creado ID {$item['id']}, Pendiente={$item['cantidad_pendiente']}\n\n";
    
    // Ver col4
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo_test]);
    $col4_nuevo = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: " . number_format($col4_nuevo, 2) . "\n";
    echo "Diferencia: " . number_format($presupuesto['col4'] - $col4_nuevo, 2) . "\n";
    echo "Esperado: 7000.00\n\n";
    
    if (abs($col4_nuevo - ($presupuesto['col4'] - 7000)) < 0.01) {
        echo "✅✅✅ TRIGGER FUNCIONÓ CORRECTAMENTE EN INSERT\n";
    } else {
        echo "❌ INSERT TRIGGER NO FUNCIONÓ\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
