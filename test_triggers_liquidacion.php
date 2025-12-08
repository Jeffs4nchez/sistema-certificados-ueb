<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== TEST: LIQUIDAR UN ITEM Y VERIFICAR TRIGGERS ===\n";

// 1. Tomar un item existente
echo "\n📌 Paso 1: Seleccionar un item para probar...\n";

$item = $db->query("
    SELECT id, monto, cantidad_liquidacion, cantidad_pendiente, codigo_completo
    FROM detalle_certificados
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "❌ No hay items para probar\n";
    exit;
}

echo "Item seleccionado:\n";
printf("  ID: %d\n", $item['id']);
printf("  Monto: %.2f\n", $item['monto']);
printf("  Liquidación actual: %.2f\n", $item['cantidad_liquidacion'] ?? 0);
printf("  Pendiente: %.2f\n", $item['cantidad_pendiente']);
printf("  Código: %s\n", $item['codigo_completo']);

// 2. Ver estado ANTES de liquidar
echo "\n📌 Paso 2: Estado ANTES de liquidar...\n";

$stmt = $db->prepare("SELECT col4, col3, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
$stmt->execute([$item['codigo_completo']]);
$presup_antes = $stmt->fetch(PDO::FETCH_ASSOC);

printf("  Presupuesto col4: %.2f\n", $presup_antes['col4'] ?? 0);
printf("  Presupuesto saldo: %.2f\n", $presup_antes['saldo_disponible'] ?? 0);

// 3. Simular liquidación: cambiar cantidad_liquidacion
echo "\n📌 Paso 3: Liquidar 50%% del item...\n";

$liquidacion_nueva = $item['monto'] * 0.5;

try {
    $stmt = $db->prepare("
        UPDATE detalle_certificados
        SET cantidad_liquidacion = ?
        WHERE id = ?
    ");
    $stmt->execute([$liquidacion_nueva, $item['id']]);
    
    printf("  ✅ Actualizado: cantidad_liquidacion = %.2f\n", $liquidacion_nueva);
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
    exit;
}

// 4. Ver estado DESPUÉS de liquidar
echo "\n📌 Paso 4: Estado DESPUÉS de liquidar...\n";

$stmt = $db->prepare("SELECT cantidad_liquidacion, cantidad_pendiente FROM detalle_certificados WHERE id = ?");
$stmt->execute([$item['id']]);
$item_despues = $stmt->fetch(PDO::FETCH_ASSOC);

printf("  Item cantidad_liquidacion: %.2f\n", $item_despues['cantidad_liquidacion']);
printf("  Item cantidad_pendiente: %.2f (debería ser %.2f)\n", 
    $item_despues['cantidad_pendiente'],
    $item['monto'] - $liquidacion_nueva
);

$stmt = $db->prepare("SELECT col4, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
$stmt->execute([$item['codigo_completo']]);
$presup_despues = $stmt->fetch(PDO::FETCH_ASSOC);

printf("  Presupuesto col4: %.2f (debería ser %.2f)\n", 
    $presup_despues['col4'] ?? 0,
    $liquidacion_nueva
);
printf("  Presupuesto saldo: %.2f\n", $presup_despues['saldo_disponible'] ?? 0);

// 5. Verificación
echo "\n📌 Paso 5: Verificación de triggers...\n";

$pendiente_ok = abs($item_despues['cantidad_pendiente'] - ($item['monto'] - $liquidacion_nueva)) < 0.01;
$col4_ok = abs($presup_despues['col4'] - $liquidacion_nueva) < 0.01;

if ($pendiente_ok && $col4_ok) {
    echo "✅ TRIGGERS FUNCIONANDO CORRECTAMENTE\n";
} else {
    echo "❌ PROBLEMAS CON LOS TRIGGERS:\n";
    if (!$pendiente_ok) {
        echo "   - cantidad_pendiente no se recalculó correctamente\n";
    }
    if (!$col4_ok) {
        echo "   - col4 no se actualizó correctamente\n";
    }
}

// 6. Restaurar estado original
echo "\n📌 Paso 6: Restaurar estado original...\n";

try {
    $stmt = $db->prepare("
        UPDATE detalle_certificados
        SET cantidad_liquidacion = ?
        WHERE id = ?
    ");
    $stmt->execute([$item['cantidad_liquidacion'] ?? 0, $item['id']]);
    echo "✅ Restaurado\n";
} catch (Exception $e) {
    echo "⚠️  Error al restaurar: " . $e->getMessage() . "\n";
}

?>
