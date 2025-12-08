<?php
/**
 * SOLUCIÓN FINAL: ASEGURAR AMBOS TRIGGERS FUNCIONAN
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "SOLUCIÓN FINAL: TRIGGERS CANTIDAD_PENDIENTE + COL4\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. ELIMINAR TODOS LOS TRIGGERS EXISTENTES
    echo "1️⃣  LIMPIAR TODOS LOS TRIGGERS\n";
    echo str_repeat("-", 100) . "\n";
    
    $triggers_to_drop = [
        'trg_item_delete',
        'trg_item_insert',
        'trg_item_update',
        'trg_pendiente_insert',
        'trg_pendiente_update',
        'trg_pendiente_delete',
        'trg_detalle_before_insert',
        'trg_detalle_before_update',
        'trg_detalle_before_delete',
        'trigger_detalle_cantidad_pendiente',
        'trg_update_cert_totales_insert',
        'trg_update_cert_totales_update',
        'trg_update_cert_totales_delete',
    ];
    
    foreach ($triggers_to_drop as $trg) {
        try {
            $db->exec("DROP TRIGGER IF EXISTS $trg ON detalle_certificados CASCADE");
        } catch (Exception $e) {
            // Ignorar si el trigger no existe
        }
    }
    echo "✅ Todos los triggers eliminados\n\n";
    
    // 2. CREAR FUNCIÓN 1: Calcula cantidad_pendiente
    echo "2️⃣  CREAR FUNCIÓN 1: fn_trigger_detalle_cantidad_pendiente\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP FUNCTION IF EXISTS fn_trigger_detalle_cantidad_pendiente() CASCADE");
    
    $sql1 = "
        CREATE FUNCTION fn_trigger_detalle_cantidad_pendiente()
        RETURNS TRIGGER AS \$func\$
        BEGIN
            NEW.cantidad_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
            RETURN NEW;
        END;
        \$func\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($sql1);
    echo "✅ Función creada\n\n";
    
    // 3. CREAR TRIGGER 1: BEFORE INSERT/UPDATE para calcular cantidad_pendiente
    echo "3️⃣  CREAR TRIGGER 1: BEFORE INSERT/UPDATE\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trigger_detalle_cantidad_pendiente
        BEFORE INSERT OR UPDATE OF monto, cantidad_liquidacion
        ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_trigger_detalle_cantidad_pendiente()
    ");
    echo "✅ Trigger creado\n\n";
    
    // 4. CREAR FUNCIÓN 2: Actualiza col4 AFTER INSERT
    echo "4️⃣  CREAR FUNCIÓN 2: fn_actualizar_col4_insert\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP FUNCTION IF EXISTS fn_actualizar_col4_insert() CASCADE");
    
    $sql2 = "
        CREATE FUNCTION fn_actualizar_col4_insert()
        RETURNS TRIGGER AS \$func\$
        BEGIN
            -- NEW.cantidad_pendiente YA FUE CALCULADO por el BEFORE trigger
            IF NEW.codigo_completo IS NOT NULL AND NEW.cantidad_pendiente > 0 THEN
                UPDATE presupuesto_items
                SET col4 = COALESCE(col4, 0) - NEW.cantidad_pendiente
                WHERE codigo_completo = NEW.codigo_completo;
            END IF;
            
            RETURN NEW;
        END;
        \$func\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($sql2);
    echo "✅ Función creada\n\n";
    
    // 5. CREAR TRIGGER 2: AFTER INSERT para actualizar col4
    echo "5️⃣  CREAR TRIGGER 2: AFTER INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trg_actualizar_col4_insert
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_insert()
    ");
    echo "✅ Trigger creado\n\n";
    
    // 6. CREAR FUNCIÓN 3: Actualiza col4 AFTER UPDATE
    echo "6️⃣  CREAR FUNCIÓN 3: fn_actualizar_col4_update\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP FUNCTION IF EXISTS fn_actualizar_col4_update() CASCADE");
    
    $sql3 = "
        CREATE FUNCTION fn_actualizar_col4_update()
        RETURNS TRIGGER AS \$func\$
        DECLARE
            v_diferencia NUMERIC;
        BEGIN
            -- cantidad_pendiente YA FUE RECALCULADA por BEFORE trigger
            IF NEW.codigo_completo IS NOT NULL THEN
                -- Diferencia entre nueva pendiente y vieja pendiente
                v_diferencia := NEW.cantidad_pendiente - OLD.cantidad_pendiente;
                
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
    
    $db->exec($sql3);
    echo "✅ Función creada\n\n";
    
    // 7. CREAR TRIGGER 3: AFTER UPDATE para actualizar col4
    echo "7️⃣  CREAR TRIGGER 3: AFTER UPDATE\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trg_actualizar_col4_update
        AFTER UPDATE OF monto, cantidad_liquidacion, cantidad_pendiente ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_update()
    ");
    echo "✅ Trigger creado\n\n";
    
    // 8. CREAR FUNCIÓN 4: Restaura col4 AFTER DELETE
    echo "8️⃣  CREAR FUNCIÓN 4: fn_actualizar_col4_delete\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP FUNCTION IF EXISTS fn_actualizar_col4_delete() CASCADE");
    
    $sql4 = "
        CREATE FUNCTION fn_actualizar_col4_delete()
        RETURNS TRIGGER AS \$func\$
        BEGIN
            -- Al eliminar, sumamos la cantidad_pendiente de vuelta
            IF OLD.codigo_completo IS NOT NULL AND OLD.cantidad_pendiente > 0 THEN
                UPDATE presupuesto_items
                SET col4 = COALESCE(col4, 0) + OLD.cantidad_pendiente
                WHERE codigo_completo = OLD.codigo_completo;
            END IF;
            
            RETURN OLD;
        END;
        \$func\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($sql4);
    echo "✅ Función creada\n\n";
    
    // 9. CREAR TRIGGER 4: AFTER DELETE para restaurar col4
    echo "9️⃣  CREAR TRIGGER 4: AFTER DELETE\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trg_actualizar_col4_delete
        AFTER DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_delete()
    ");
    echo "✅ Trigger creado\n\n";
    
    // 10. PROBAR INSERT
    echo "🔟 PROBAR INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $codigo_test = 'TEST-FINAL-' . date('YmdHis');
    
    $stmt = $db->prepare("
        INSERT INTO presupuesto_items 
        (codigo_completo, col4, col1, col2, col3, fecha_creacion)
        VALUES (?, ?, 1, 1, 1, NOW())
        RETURNING id, col4
    ");
    $stmt->execute([$codigo_test, 50000]);
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
    $stmt->execute(['TEST-' . uniqid(), 'TEST', 'TEST', 'Test Final', date('Y-m-d'), 10000, 'admin']);
    $cert_id = $stmt->fetchColumn();
    
    // Insertar item
    echo "Insertando item: Monto=10000, Liquidado=0\n";
    $stmt = $db->prepare("
        INSERT INTO detalle_certificados
        (certificado_id, monto, cantidad_liquidacion, codigo_completo)
        VALUES (?, ?, ?, ?)
        RETURNING id, cantidad_pendiente
    ");
    $stmt->execute([$cert_id, 10000, 0, $codigo_test]);
    $item = $stmt->fetch();
    
    echo "✅ Item creado ID {$item['id']}\n";
    echo "✅ Cantidad Pendiente (calculada): {$item['cantidad_pendiente']}\n\n";
    
    // Ver col4
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo_test]);
    $col4_nuevo = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: " . number_format($col4_nuevo, 2) . "\n";
    echo "Diferencia: " . number_format($presupuesto['col4'] - $col4_nuevo, 2) . "\n";
    echo "Esperado: 10000.00\n\n";
    
    if (abs($col4_nuevo - ($presupuesto['col4'] - 10000)) < 0.01) {
        echo "✅✅✅ TODO FUNCIONÓ CORRECTAMENTE\n";
        echo "El sistema ahora actualiza col4 cuando se crea un item\n";
    } else {
        echo "❌ AÚN HAY PROBLEMA\n";
        echo "   Debería ser: " . number_format($presupuesto['col4'] - 10000, 2) . "\n";
        echo "   Es: " . number_format($col4_nuevo, 2) . "\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
