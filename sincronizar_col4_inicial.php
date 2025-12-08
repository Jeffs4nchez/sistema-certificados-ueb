<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== SINCRONIZAR COL4 CON LIQUIDACIONES ===\n";

// 1. Ver datos actuales
echo "\n📌 Paso 1: Estado actual de items y presupuestos...\n";

$items = $db->query("
    SELECT 
        dc.id, dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
        dc.codigo_completo,
        pi.col4, pi.col3, pi.saldo_disponible
    FROM detalle_certificados dc
    LEFT JOIN presupuesto_items pi ON pi.codigo_completo = dc.codigo_completo
    ORDER BY dc.id DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

echo "Item | Monto | Liquidado | Pendiente | Col4_Act | Col4_Esperado\n";
echo str_repeat("-", 80) . "\n";

foreach ($items as $item) {
    $esperado = $item['monto'] - $item['cantidad_pendiente'];
    $ok = (abs($item['col4'] - $esperado) < 0.01) ? "✅" : "❌";
    printf("%d | %.0f | %.0f | %.0f | %.0f | %.0f %s\n",
        $item['id'],
        $item['monto'],
        $item['cantidad_liquidacion'] ?? 0,
        $item['cantidad_pendiente'],
        $item['col4'] ?? 0,
        $esperado,
        $ok
    );
}

// 2. Recalcular col4 para todos los items
echo "\n📌 Paso 2: Recalcular col4 en presupuesto_items...\n";

try {
    // Agrupar por codigo_completo y sumar lo liquidado
    $db->exec("
        UPDATE presupuesto_items pi
        SET 
            col4 = COALESCE((
                SELECT SUM(dc.monto - dc.cantidad_pendiente)
                FROM detalle_certificados dc
                WHERE dc.codigo_completo = pi.codigo_completo
            ), 0),
            saldo_disponible = COALESCE(col3, 0) - COALESCE((
                SELECT SUM(dc.monto - dc.cantidad_pendiente)
                FROM detalle_certificados dc
                WHERE dc.codigo_completo = pi.codigo_completo
            ), 0),
            fecha_actualizacion = NOW()
        WHERE codigo_completo IN (
            SELECT DISTINCT codigo_completo FROM detalle_certificados
        )
    ");
    
    echo "✅ col4 recalculado para todos los presupuestos\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 3. Verificar después de la sincronización
echo "\n📌 Paso 3: Verificar después de sincronización...\n";

$items_after = $db->query("
    SELECT 
        dc.id, dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
        dc.codigo_completo,
        pi.col4, pi.saldo_disponible
    FROM detalle_certificados dc
    LEFT JOIN presupuesto_items pi ON pi.codigo_completo = dc.codigo_completo
    ORDER BY dc.id DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

echo "Item | Monto | Liquidado | Pendiente | Col4 | Estado\n";
echo str_repeat("-", 80) . "\n";

foreach ($items_after as $item) {
    $esperado = $item['monto'] - $item['cantidad_pendiente'];
    $ok = (abs($item['col4'] - $esperado) < 0.01) ? "✅" : "❌";
    printf("%d | %.0f | %.0f | %.0f | %.0f | %s\n",
        $item['id'],
        $item['monto'],
        $item['cantidad_liquidacion'] ?? 0,
        $item['cantidad_pendiente'],
        $item['col4'] ?? 0,
        $ok
    );
}

echo "\n✅ Sincronización completada.\n";

echo "\n📋 Ahora los triggers harán:\n";
echo "   - INSERT: suma lo liquidado a col4\n";
echo "   - UPDATE: ajusta col4 por diferencia de liquidación\n";
echo "   - DELETE: resta lo que estaba liquidado\n";

?>
