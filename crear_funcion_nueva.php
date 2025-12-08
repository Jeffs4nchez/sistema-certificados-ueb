<?php
/**
 * CREAR FUNCIÓN CORRECTAMENTE
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "CREAR Y VERIFICAR FUNCIÓN\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Eliminar triggers viejos
    echo "1️⃣  ELIMINAR TRIGGERS VIEJOS\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP TRIGGER IF EXISTS trg_pendiente_insert ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trg_pendiente_update ON detalle_certificados CASCADE");
    $db->exec("DROP TRIGGER IF EXISTS trg_pendiente_delete ON detalle_certificados CASCADE");
    echo "✅ Triggers eliminados\n\n";
    
    // 2. Eliminar función vieja
    echo "2️⃣  ELIMINAR FUNCIÓN VIEJA\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP FUNCTION IF EXISTS fn_restar_pendiente_col4() CASCADE");
    echo "✅ Función eliminada\n\n";
    
    // 3. Crear función NUEVA
    echo "3️⃣  CREAR FUNCIÓN NUEVA\n";
    echo str_repeat("-", 100) . "\n";
    
    $sql = "
        CREATE FUNCTION fn_restar_pendiente_col4()
        RETURNS TRIGGER AS \$func\$
        DECLARE
            v_cantidad NUMERIC;
            v_codigo VARCHAR;
        BEGIN
            IF TG_OP = 'DELETE' THEN
                v_codigo := OLD.codigo_completo;
                v_cantidad := OLD.cantidad_pendiente;
                -- En DELETE, sumamos de vuelta
                IF v_codigo IS NOT NULL AND v_cantidad > 0 THEN
                    UPDATE presupuesto_items
                    SET col4 = col4 + v_cantidad
                    WHERE codigo_completo = v_codigo;
                END IF;
            ELSE
                v_codigo := NEW.codigo_completo;
                v_cantidad := NEW.cantidad_pendiente;
                -- En INSERT/UPDATE, restamos
                IF v_codigo IS NOT NULL AND v_cantidad > 0 THEN
                    UPDATE presupuesto_items
                    SET col4 = col4 - v_cantidad
                    WHERE codigo_completo = v_codigo;
                END IF;
            END IF;
            
            RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
        END;
        \$func\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($sql);
    echo "✅ Función creada\n\n";
    
    // 4. Ver función en BD
    echo "4️⃣  VERIFICAR FUNCIÓN EN BD\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT pg_get_functiondef(oid)
        FROM pg_proc
        WHERE proname = 'fn_restar_pendiente_col4'
    ");
    
    $result = $stmt->fetch();
    if ($result) {
        echo "✅ Función existe en BD:\n\n";
        echo $result[0] . "\n\n";
    } else {
        echo "❌ Función NO existe en BD\n\n";
    }
    
    // 5. Crear triggers
    echo "5️⃣  CREAR TRIGGERS\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trg_pendiente_insert
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_restar_pendiente_col4()
    ");
    echo "✅ Trigger INSERT creado\n";
    
    $db->exec("
        CREATE TRIGGER trg_pendiente_update
        AFTER UPDATE OF cantidad_liquidacion, cantidad_pendiente ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_restar_pendiente_col4()
    ");
    echo "✅ Trigger UPDATE creado\n";
    
    $db->exec("
        CREATE TRIGGER trg_pendiente_delete
        AFTER DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_restar_pendiente_col4()
    ");
    echo "✅ Trigger DELETE creado\n\n";
    
    // 6. Probar con INSERT
    echo "6️⃣  PROBAR CON INSERT NUEVO\n";
    echo str_repeat("-", 100) . "\n";
    
    $codigo_test = 'TEST-FINAL-' . date('YmdHis');
    
    $stmt = $db->prepare("
        INSERT INTO presupuesto_items 
        (codigo_completo, col4, col1, col2, col3, fecha_creacion)
        VALUES (?, ?, 1, 1, 1, NOW())
        RETURNING id, col4
    ");
    $stmt->execute([$codigo_test, 15000]);
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
    $stmt->execute(['TEST-' . uniqid(), 'TEST', 'TEST', 'Test Final', date('Y-m-d'), 2500, 'admin']);
    $cert_id = $stmt->fetchColumn();
    
    // Insertar item
    $stmt = $db->prepare("
        INSERT INTO detalle_certificados
        (certificado_id, monto, cantidad_liquidacion, codigo_completo)
        VALUES (?, ?, ?, ?)
        RETURNING id, cantidad_pendiente
    ");
    $stmt->execute([$cert_id, 2500, 0, $codigo_test]);
    $item = $stmt->fetch();
    
    echo "Item creado:\n";
    echo "  ID: {$item['id']}\n";
    echo "  Monto: 2500\n";
    echo "  Liquidado: 0\n";
    echo "  Pendiente: {$item['cantidad_pendiente']}\n\n";
    
    // Ver col4
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo_test]);
    $col4_nuevo = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: " . number_format($col4_nuevo, 2) . "\n";
    echo "Diferencia: " . number_format($presupuesto['col4'] - $col4_nuevo, 2) . "\n";
    echo "Esperado: 2500.00\n\n";
    
    if (abs($col4_nuevo - ($presupuesto['col4'] - 2500)) < 0.01) {
        echo "✅✅✅ TRIGGER FUNCIONÓ CORRECTAMENTE\n";
    } else {
        echo "❌ TRIGGER NO FUNCIONÓ\n";
        echo "   Debería ser: " . number_format($presupuesto['col4'] - 2500, 2) . "\n";
        echo "   Es: " . number_format($col4_nuevo, 2) . "\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
