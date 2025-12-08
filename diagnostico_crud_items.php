<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== DIAGNOSTICO: TRIGGERS EN CRUD DE ITEMS ===\n";

// 1. Ver triggers activos
echo "\n📌 Triggers en detalle_certificados:\n";

$triggers = $db->query("
    SELECT 
        trigger_name, 
        event_manipulation, 
        action_timing,
        event_object_table
    FROM information_schema.triggers
    WHERE trigger_schema = 'public' AND event_object_table = 'detalle_certificados'
    ORDER BY event_manipulation, action_timing DESC
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($triggers as $t) {
    printf("  %-45s | %-10s | %-10s\n",
        $t['trigger_name'],
        $t['event_manipulation'],
        $t['action_timing']
    );
}

// 2. Verificar si hay conflictos en el CRUD
echo "\n📌 Análisis de operaciones CRUD:\n";

echo "\nCREATE (INSERT):\n";
$inserts = array_filter($triggers, fn($t) => $t['event_manipulation'] == 'INSERT');
if (count($inserts) > 1) {
    echo "  ⚠️  Múltiples triggers en INSERT:\n";
    foreach ($inserts as $t) {
        echo "      - " . $t['trigger_name'] . " (" . $t['action_timing'] . ")\n";
    }
} else {
    echo "  ✅ Un solo trigger en INSERT\n";
}

echo "\nREAD (SELECT):\n";
echo "  ✅ SELECT no dispara triggers\n";

echo "\nUPDATE:\n";
$updates = array_filter($triggers, fn($t) => $t['event_manipulation'] == 'UPDATE');
if (count($updates) > 2) {
    echo "  ⚠️  Múltiples triggers en UPDATE:\n";
    foreach ($updates as $t) {
        echo "      - " . $t['trigger_name'] . " (" . $t['action_timing'] . ")\n";
    }
} else {
    echo "  ✅ Triggers en UPDATE controlados\n";
}

echo "\nDELETE:\n";
$deletes = array_filter($triggers, fn($t) => $t['event_manipulation'] == 'DELETE');
if (count($deletes) > 1) {
    echo "  ⚠️  Múltiples triggers en DELETE:\n";
    foreach ($deletes as $t) {
        echo "      - " . $t['trigger_name'] . " (" . $t['action_timing'] . ")\n";
    }
} else {
    echo "  ✅ Un solo trigger en DELETE\n";
}

// 3. Problema identificado
echo "\n\n📌 PROBLEMA IDENTIFICADO:\n";
echo "  Los triggers de detalle_certificados intentan actualizar presupuesto_items,\n";
echo "  pero:\n";
echo "    1. ¿Están habilitados realmente?\n";
echo "    2. ¿Los códigos_completos existen en presupuesto_items?\n";
echo "    3. ¿Las funciones están correctas?\n";

// 4. Test: Ver si un item tiene presupuesto
echo "\n📌 Test: Verificar si items tienen presupuesto sincronizado...\n";

$items_test = $db->query("
    SELECT 
        dc.id, dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
        dc.codigo_completo,
        pi.col4 as presup_col4
    FROM detalle_certificados dc
    LEFT JOIN presupuesto_items pi ON pi.codigo_completo = dc.codigo_completo
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($items_test)) {
    echo "  ❌ No hay items en detalle_certificados\n";
} else {
    foreach ($items_test as $item) {
        $ok = ($item['presup_col4'] !== null) ? "✅" : "❌";
        printf("  %s Item %d: codigo=%s → presupuesto col4 existe: %s\n",
            $ok,
            $item['id'],
            substr($item['codigo_completo'], 0, 20),
            $item['presup_col4'] ?? 'NO ENCONTRADO'
        );
    }
}

echo "\n✅ Diagnóstico completado.\n";

?>
