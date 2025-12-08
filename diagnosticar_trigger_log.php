<?php
/**
 * DIAGNOSTICAR: Por qué la función no actualiza col4
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "DIAGNOSTICAR: FUNCIÓN Y TRIGGERS\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Ver si la función existe
    echo "1️⃣  VERIFICAR SI FUNCIÓN EXISTE\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT routine_name, routine_type
        FROM information_schema.routines
        WHERE routine_name = 'fn_restar_pendiente_col4'
    ");
    
    $func = $stmt->fetch();
    if ($func) {
        echo "✅ Función existe: {$func['routine_name']} ({$func['routine_type']})\n\n";
    } else {
        echo "❌ FUNCIÓN NO EXISTE\n\n";
    }
    
    // 2. Ver el código del trigger INSERT
    echo "2️⃣  CÓDIGO DEL TRIGGER INSERT\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT trigger_name, action_statement
        FROM information_schema.triggers
        WHERE trigger_name = 'trg_pendiente_insert'
    ");
    
    $trigger = $stmt->fetch();
    if ($trigger) {
        echo $trigger['action_statement'] ?? 'No action statement found' . "\n\n";
    }
    
    // 3. Ver un item reciente para diagnosticar valores
    echo "3️⃣  VERIFICAR ÚLTIMO ITEM CREADO\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT 
            dc.id,
            dc.monto,
            dc.cantidad_liquidacion,
            dc.cantidad_pendiente,
            dc.codigo_completo,
            pi.col4
        FROM detalle_certificados dc
        LEFT JOIN presupuesto_items pi ON pi.codigo_completo = dc.codigo_completo
        ORDER BY dc.id DESC
        LIMIT 1
    ");
    
    $item = $stmt->fetch();
    if ($item) {
        echo "Item ID: {$item['id']}\n";
        echo "  Monto: {$item['monto']}\n";
        echo "  Cantidad Liquidado: {$item['cantidad_liquidacion']}\n";
        echo "  Cantidad Pendiente: {$item['cantidad_pendiente']}\n";
        echo "  Código Completo: {$item['codigo_completo']}\n";
        echo "  Col4 en presupuesto: {$item['col4']}\n\n";
    }
    
    // 4. Crear un trigger de prueba que hace un INSERT en una tabla de LOG
    echo "4️⃣  CREAR TABLA DE LOG PARA TRIGGER\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("DROP TABLE IF EXISTS trigger_log CASCADE");
    $db->exec("
        CREATE TABLE trigger_log (
            id SERIAL PRIMARY KEY,
            trigger_name VARCHAR(100),
            operacion VARCHAR(50),
            codigo_completo VARCHAR(100),
            cantidad_pendiente NUMERIC,
            resultado VARCHAR(500),
            fecha_evento TIMESTAMP DEFAULT NOW()
        )
    ");
    echo "✅ Tabla trigger_log creada\n\n";
    
    // 5. Crear nueva función que loguee
    echo "5️⃣  CREAR FUNCIÓN CON LOG\n";
    echo str_repeat("-", 100) . "\n";
    
    $new_function = "
        DROP FUNCTION IF EXISTS fn_restar_pendiente_col4() CASCADE;
        
        CREATE FUNCTION fn_restar_pendiente_col4()
        RETURNS TRIGGER AS \$\$
        DECLARE
            v_diferencia_pendiente NUMERIC;
            v_codigo_completo VARCHAR;
            v_filas_afectadas INT;
        BEGIN
            -- Obtener datos según operación
            IF TG_OP = 'DELETE' THEN
                v_codigo_completo := OLD.codigo_completo;
                v_diferencia_pendiente := OLD.cantidad_pendiente;
            ELSE
                v_codigo_completo := NEW.codigo_completo;
                v_diferencia_pendiente := NEW.cantidad_pendiente;
            END IF;
            
            -- LOGUEAR PARA DIAGNÓSTICO
            INSERT INTO trigger_log (trigger_name, operacion, codigo_completo, cantidad_pendiente)
            VALUES ('fn_restar_pendiente_col4', TG_OP, v_codigo_completo, v_diferencia_pendiente);
            
            -- Si hay código_completo, restar cantidad_pendiente de col4 en presupuesto_items
            IF v_codigo_completo IS NOT NULL AND v_diferencia_pendiente > 0 THEN
                UPDATE presupuesto_items
                SET col4 = COALESCE(col4, 0) - v_diferencia_pendiente,
                    fecha_actualizacion = NOW()
                WHERE codigo_completo = v_codigo_completo;
                
                GET DIAGNOSTICS v_filas_afectadas = ROW_COUNT;
                
                UPDATE trigger_log 
                SET resultado = 'Actualizó ' || v_filas_afectadas || ' filas'
                WHERE trigger_name = 'fn_restar_pendiente_col4' 
                  AND codigo_completo = v_codigo_completo
                  AND fecha_evento = (SELECT MAX(fecha_evento) FROM trigger_log WHERE trigger_name = 'fn_restar_pendiente_col4');
            ELSE
                UPDATE trigger_log 
                SET resultado = 'No aplica: codigo_completo=' || COALESCE(v_codigo_completo, 'NULL') || ' pendiente=' || COALESCE(v_diferencia_pendiente::VARCHAR, 'NULL')
                WHERE trigger_name = 'fn_restar_pendiente_col4' 
                  AND codigo_completo = v_codigo_completo
                  AND fecha_evento = (SELECT MAX(fecha_evento) FROM trigger_log WHERE trigger_name = 'fn_restar_pendiente_col4');
            END IF;
            
            RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
        END;
        \$\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($new_function);
    echo "✅ Función actualizada con logging\n\n";
    
    // 6. Recrear triggers
    echo "6️⃣  RECREAR TRIGGERS\n";
    echo str_repeat("-", 100) . "\n";
    
    $db->exec("
        DROP TRIGGER IF EXISTS trg_pendiente_insert ON detalle_certificados CASCADE;
        
        CREATE TRIGGER trg_pendiente_insert
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_restar_pendiente_col4();
    ");
    echo "✅ Trigger trg_pendiente_insert recreado\n\n";
    
    // 7. Crear nuevo item de prueba
    echo "7️⃣  CREAR ITEM DE PRUEBA\n";
    echo str_repeat("-", 100) . "\n";
    
    require_once 'app/models/Certificate.php';
    $cert = new Certificate();
    
    $stmt = $db->query("SELECT id, codigo_completo, col4 FROM presupuesto_items LIMIT 1");
    $presupuesto = $stmt->fetch();
    
    $codigo = $presupuesto['codigo_completo'];
    $col4_antes = $presupuesto['col4'];
    
    echo "Presupuesto: $codigo\n";
    echo "Col4 ANTES: " . number_format($col4_antes, 2) . "\n\n";
    
    $cert_data = [
        'numero_certificado' => 'TEST-DIAGNOSTICO-' . date('YmdHis'),
        'institucion' => 'TEST',
        'seccion_memorando' => 'TEST',
        'descripcion' => 'Test con logging',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 500,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $cert->createCertificate($cert_data);
    
    $item_data = [
        'certificado_id' => $cert_id,
        'programa_codigo' => '01',
        'subprograma_codigo' => '00',
        'proyecto_codigo' => '000',
        'actividad_codigo' => '001',
        'item_codigo' => '510001',
        'ubicacion_codigo' => '0200',
        'fuente_codigo' => '001',
        'organismo_codigo' => '0000',
        'naturaleza_codigo' => '0000',
        'descripcion_item' => 'Test con logging',
        'monto' => 500,
        'codigo_completo' => $codigo,
        'cantidad_liquidacion' => 0
    ];
    
    $item_id = $cert->createDetail($item_data);
    echo "✅ Item creado: ID $item_id\n\n";
    
    // 8. Ver logs del trigger
    echo "8️⃣  LOGS DEL TRIGGER\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT * FROM trigger_log
        ORDER BY fecha_evento DESC
        LIMIT 5
    ");
    
    $logs = $stmt->fetchAll();
    foreach ($logs as $log) {
        echo "  [{$log['operacion']}] {$log['codigo_completo']}: Pendiente={$log['cantidad_pendiente']} -> {$log['resultado']}\n";
    }
    
    echo "\n";
    
    // 9. Ver col4 después
    $stmt = $db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
    $stmt->execute([$presupuesto['id']]);
    $col4_despues = $stmt->fetchColumn();
    
    echo "Col4 DESPUÉS: " . number_format($col4_despues, 2) . "\n";
    echo "Diferencia: " . number_format($col4_antes - $col4_despues, 2) . "\n";
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
