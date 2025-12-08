<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== IMPLEMENTANDO CÁLCULOS DE CANTIDAD_PENDIENTE ===\n";

// 1. Crear trigger que recalcule cantidad_pendiente cuando cambia cantidad_liquidacion
echo "\n📌 Paso 1: Crear trigger para recalcular cantidad_pendiente...\n";

$triggers = [
    // Trigger en UPDATE de detalle_certificados para recalcular cantidad_pendiente
    "CREATE OR REPLACE FUNCTION fn_trigger_recalcula_pendiente()
    RETURNS TRIGGER AS $$
    BEGIN
        -- Cuando cambia cantidad_liquidacion, recalcular cantidad_pendiente
        IF NEW.cantidad_liquidacion IS DISTINCT FROM OLD.cantidad_liquidacion THEN
            NEW.cantidad_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
        END IF;
        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;",
    
    "DROP TRIGGER IF EXISTS trigger_recalcula_pendiente ON detalle_certificados;",
    
    "CREATE TRIGGER trigger_recalcula_pendiente
    BEFORE UPDATE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_recalcula_pendiente();",
    
    // Trigger que actualiza total_pendiente en certificados
    "CREATE OR REPLACE FUNCTION fn_trigger_actualiza_total_pendiente()
    RETURNS TRIGGER AS $$
    DECLARE
        v_suma_pendiente NUMERIC;
    BEGIN
        -- Calcular suma de cantidad_pendiente de todos los items
        SELECT COALESCE(SUM(cantidad_pendiente), 0)
        INTO v_suma_pendiente
        FROM detalle_certificados
        WHERE certificado_id = NEW.certificado_id;
        
        -- Actualizar total_pendiente en certificados
        UPDATE certificados
        SET total_pendiente = v_suma_pendiente
        WHERE id = NEW.certificado_id;
        
        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql;",
    
    "DROP TRIGGER IF EXISTS trigger_actualiza_total_pendiente ON detalle_certificados;",
    
    "CREATE TRIGGER trigger_actualiza_total_pendiente
    AFTER INSERT OR UPDATE OR DELETE ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_actualiza_total_pendiente();"
];

foreach ($triggers as $sql) {
    try {
        $db->exec($sql);
        echo "✅ " . substr($sql, 0, 50) . "...\n";
    } catch (Exception $e) {
        echo "⚠️  Error: " . $e->getMessage() . "\n";
    }
}

// 2. Corregir los datos existentes
echo "\n📌 Paso 2: Corregir cantidad_pendiente existente...\n";

try {
    // Recalcular cantidad_pendiente para todos los items
    $db->exec("
        UPDATE detalle_certificados
        SET cantidad_pendiente = monto - COALESCE(cantidad_liquidacion, 0)
        WHERE cantidad_pendiente != (monto - COALESCE(cantidad_liquidacion, 0))
    ");
    
    $affected = $db->query("
        SELECT COUNT(*) as cnt FROM detalle_certificados
        WHERE cantidad_pendiente = (monto - COALESCE(cantidad_liquidacion, 0))
    ")->fetch()['cnt'];
    
    echo "✅ Cantidad_pendiente recalculada correctamente en " . $affected . " items\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 3. Actualizar total_pendiente en certificados
echo "\n📌 Paso 3: Actualizar total_pendiente en certificados...\n";

try {
    $db->exec("
        UPDATE certificados c
        SET total_pendiente = (
            SELECT COALESCE(SUM(cantidad_pendiente), 0)
            FROM detalle_certificados
            WHERE certificado_id = c.id
        )
    ");
    
    echo "✅ Total_pendiente actualizado en todos los certificados\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 4. Verificar resultados
echo "\n📌 Paso 4: Verificar resultados...\n";

$resultado = $db->query("
    SELECT 
        c.id, c.numero_certificado, c.monto_total, c.total_pendiente,
        SUM(dc.monto) as suma_montos,
        SUM(dc.cantidad_liquidacion) as suma_liquidaciones,
        SUM(dc.cantidad_pendiente) as suma_pendiente
    FROM certificados c
    LEFT JOIN detalle_certificados dc ON dc.certificado_id = c.id
    GROUP BY c.id
    ORDER BY c.id DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo "\n📋 CERTIFICADOS ACTUALIZADOS:\n";
echo str_repeat("-", 130) . "\n";
printf("%-5s | %-20s | %-12s | %-15s | %-12s | %-15s | %-15s\n",
    "ID", "NUMERO", "MONTO_TOTAL", "TOTAL_PENDIENTE", "MONTOS", "LIQUIDACIONES", "SUMA_PENDIENTE");
echo str_repeat("-", 130) . "\n";

foreach ($resultado as $row) {
    // Verificar si total_pendiente coincide con suma
    $ok = abs($row['total_pendiente'] - $row['suma_pendiente']) < 0.01 ? "✅" : "❌";
    printf("%-5s | %-20s | %-12.2f | %-15.2f | %-12.2f | %-15.2f | %-15.2f %s\n",
        $row['id'],
        $row['numero_certificado'],
        $row['monto_total'],
        $row['total_pendiente'],
        $row['suma_montos'],
        $row['suma_liquidaciones'],
        $row['suma_pendiente'],
        $ok
    );
}

// 5. Verificar detalles
echo "\n📝 DETALLES DE ITEMS:\n";
echo str_repeat("-", 140) . "\n";
printf("%-5s | %-20s | %-10s | %-15s | %-15s | %-20s | %-15s\n",
    "ID", "DESCRIPCION", "MONTO", "LIQUIDADO", "PENDIENTE", "CALC_CORRECTO", "ESTADO");
echo str_repeat("-", 140) . "\n";

$detalles = $db->query("
    SELECT 
        id, descripcion_item, monto, cantidad_liquidacion, cantidad_pendiente,
        (monto - COALESCE(cantidad_liquidacion, 0)) as pendiente_correcto
    FROM detalle_certificados
    ORDER BY certificado_id DESC, id ASC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($detalles as $row) {
    $ok = abs($row['cantidad_pendiente'] - $row['pendiente_correcto']) < 0.01 ? "✅" : "❌";
    printf("%-5s | %-20s | %-10.2f | %-15.2f | %-15.2f | %-20.2f | %s\n",
        $row['id'],
        substr($row['descripcion_item'], 0, 18),
        $row['monto'],
        $row['cantidad_liquidacion'] ?? 0,
        $row['cantidad_pendiente'],
        $row['pendiente_correcto'],
        $ok
    );
}

echo "\n✅ Implementación completada.\n";
?>
