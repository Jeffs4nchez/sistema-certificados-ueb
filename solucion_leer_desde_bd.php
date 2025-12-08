<?php
/**
 * CORREGIR: LEER cantidad_pendiente DESDE LA BD EN TRIGGER AFTER
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "SOLUCIÓN: LEER cantidad_pendiente DESDE LA BD\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Eliminar trigger viejo
    echo "1️⃣  ELIMINAR TRIGGER VIEJO\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP TRIGGER IF EXISTS trg_pendiente_insert ON detalle_certificados CASCADE");
    echo "✅ Trigger eliminado\n\n";
    
    // 2. Crear función NUEVA que lee desde BD
    echo "2️⃣  CREAR FUNCIÓN NUEVA (LEER DESDE BD)\n";
    echo str_repeat("-", 100) . "\n";
    
    $sql = "
        CREATE OR REPLACE FUNCTION fn_restar_pendiente_col4()
        RETURNS TRIGGER AS \$func\$
        DECLARE
            v_cantidad_pendiente NUMERIC;
            v_codigo VARCHAR;
            v_sql TEXT;
        BEGIN
            IF TG_OP = 'DELETE' THEN
                v_codigo := OLD.codigo_completo;
                v_cantidad_pendiente := OLD.cantidad_pendiente;
                -- En DELETE, sumamos de vuelta
                IF v_codigo IS NOT NULL AND v_cantidad_pendiente > 0 THEN
                    UPDATE presupuesto_items
                    SET col4 = col4 + v_cantidad_pendiente
                    WHERE codigo_completo = v_codigo;
                END IF;
            ELSE
                v_codigo := NEW.codigo_completo;
                -- LEER cantidad_pendiente desde BD (está en el registro que acabamos de insertar)
                SELECT cantidad_pendiente INTO v_cantidad_pendiente
                FROM detalle_certificados
                WHERE id = NEW.id;
                
                -- En INSERT/UPDATE, restamos
                IF v_codigo IS NOT NULL AND v_cantidad_pendiente IS NOT NULL AND v_cantidad_pendiente > 0 THEN
                    UPDATE presupuesto_items
                    SET col4 = col4 - v_cantidad_pendiente
                    WHERE codigo_completo = v_codigo;
                END IF;
            END IF;
            
            RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
        END;
        \$func\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($sql);
    echo "✅ Función creada\n\n";
    
    // 3. Crear trigger
    echo "3️⃣  CREAR TRIGGER\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        CREATE TRIGGER trg_pendiente_insert
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_restar_pendiente_col4()
    ");
    echo "✅ Trigger creado\n\n";
    
    // 4. Probar
    echo "4️⃣  PROBAR CON INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $codigo_test = 'TEST-SOLUCION-' . date('YmdHis');
    
    $stmt = $db->prepare("
        INSERT INTO presupuesto_items 
        (codigo_completo, col4, col1, col2, col3, fecha_creacion)
        VALUES (?, ?, 1, 1, 1, NOW())
        RETURNING id, col4
    ");
    $stmt->execute([$codigo_test, 20000]);
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
    $stmt->execute(['TEST-' . uniqid(), 'TEST', 'TEST', 'Test Solución', date('Y-m-d'), 5000, 'admin']);
    $cert_id = $stmt->fetchColumn();
    
    // Insertar item
    $stmt = $db->prepare("
        INSERT INTO detalle_certificados
        (certificado_id, monto, cantidad_liquidacion, codigo_completo)
        VALUES (?, ?, ?, ?)
        RETURNING id, cantidad_pendiente
    ");
    $stmt->execute([$cert_id, 5000, 0, $codigo_test]);
    $item = $stmt->fetch();
    
    echo "Item creado:\n";
    echo "  ID: {$item['id']}\n";
    echo "  Pendiente: {$item['cantidad_pendiente']}\n\n";
    
    // Ver col4
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo_test]);
    $col4_nuevo = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: " . number_format($col4_nuevo, 2) . "\n";
    echo "Diferencia: " . number_format($presupuesto['col4'] - $col4_nuevo, 2) . "\n";
    echo "Esperado: 5000.00\n\n";
    
    if (abs($col4_nuevo - ($presupuesto['col4'] - 5000)) < 0.01) {
        echo "✅✅✅ TRIGGER FUNCIONÓ CORRECTAMENTE\n";
    } else {
        echo "❌ TRIGGER NO FUNCIONÓ\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
