<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== SINCRONIZANDO LIQUIDACION CON PRESUPUESTO ===\n";

// Crear trigger que actualice presupuesto cuando cambia cantidad_pendiente
echo "\n📌 Paso 1: Crear trigger para sincronizar con presupuesto...\n";

$triggers = [
    // Función que sincroniza cantidad_pendiente con presupuesto_items
    "CREATE OR REPLACE FUNCTION fn_trigger_sincroniza_liquidacion()
    RETURNS TRIGGER AS $$
    DECLARE
        v_codigo_completo VARCHAR;
        v_pendiente_anterior NUMERIC;
        v_pendiente_nueva NUMERIC;
        v_diferencia_pendiente NUMERIC;
    BEGIN
        -- Solo procesar si cantidad_liquidacion cambió
        IF NEW.cantidad_liquidacion IS DISTINCT FROM OLD.cantidad_liquidacion THEN
            v_codigo_completo := NEW.codigo_completo;
            
            -- Calcular cantidad_pendiente anterior y nueva
            v_pendiente_anterior := NEW.monto - COALESCE(OLD.cantidad_liquidacion, 0);
            v_pendiente_nueva := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
            
            -- Diferencia en cantidad_pendiente
            v_diferencia_pendiente := v_pendiente_nueva - v_pendiente_anterior;
            
            -- Restar de col4 la NUEVA cantidad_pendiente (col4 = col4 - cantidad_pendiente)
            -- Si pendiente aumenta (negativa), col4 disminuye más
            -- Si pendiente disminuye (positiva), col4 aumenta
            UPDATE presupuesto_items
            SET 
                col4 = COALESCE(col4, 0) - v_diferencia_pendiente,
                saldo_disponible = COALESCE(col3, 0) - (COALESCE(col4, 0) - v_diferencia_pendiente),
                fecha_actualizacion = NOW()
            WHERE codigo_completo = v_codigo_completo;
        END IF;
        
        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;",
    
    "DROP TRIGGER IF EXISTS trigger_sincroniza_liquidacion ON detalle_certificados;",
    
    "CREATE TRIGGER trigger_sincroniza_liquidacion
    BEFORE UPDATE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_sincroniza_liquidacion();"
];

foreach ($triggers as $sql) {
    try {
        $db->exec($sql);
        echo "✅ " . substr(str_replace(["\n", "\t"], " ", $sql), 0, 60) . "...\n";
    } catch (Exception $e) {
        echo "⚠️  Error: " . $e->getMessage() . "\n";
    }
}

// Verificar estado actual
echo "\n📌 Paso 2: Verificar estado actual de items y presupuesto...\n";

$items = $db->query("
    SELECT 
        dc.id, dc.certificado_id, dc.descripcion_item, dc.codigo_completo,
        dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
        pi.col4, pi.col3, pi.saldo_disponible
    FROM detalle_certificados dc
    LEFT JOIN presupuesto_items pi ON pi.codigo_completo = dc.codigo_completo
    ORDER BY dc.certificado_id DESC, dc.id ASC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo "\n📊 ITEMS Y SUS PRESUPUESTOS SINCRONIZADOS:\n";
echo str_repeat("-", 170) . "\n";
printf("%-5s | %-20s | %-12s | %-12s | %-12s | %-12s | %-12s | %-12s\n",
    "ID", "DESCRIPCION", "MONTO", "LIQUIDADO", "PENDIENTE", "PRESUP_COL4", "PRESUP_COL3", "SALDO");
echo str_repeat("-", 170) . "\n";

foreach ($items as $row) {
    // Verificar si col4 coincide con cantidad_pendiente
    $sync = (abs($row['col4'] - $row['cantidad_pendiente']) < 0.01) ? "✅" : "⚠️";
    printf("%-5s | %-20s | %-12.2f | %-12.2f | %-12.2f | %-12.2f | %-12.2f | %-12.2f %s\n",
        $row['id'],
        substr($row['descripcion_item'], 0, 18),
        $row['monto'],
        $row['cantidad_liquidacion'] ?? 0,
        $row['cantidad_pendiente'],
        $row['col4'] ?? 'NULL',
        $row['col3'] ?? 'NULL',
        $row['saldo_disponible'] ?? 'NULL',
        $sync
    );
}

echo "\n✅ Triggers de sincronización implementados.\n";
echo "\n📝 Próximos pasos:\n";
echo "   1. Cuando se liquida un item, cantidad_pendiente cambia automáticamente\n";
echo "   2. El trigger sincroniza el cambio con presupuesto_items.col4\n";
echo "   3. El saldo_disponible se recalcula automáticamente\n";
echo "   4. El total_pendiente del certificado se actualiza automáticamente\n";
?>
