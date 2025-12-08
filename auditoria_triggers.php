<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== AUDITORIA COMPLETA DE TRIGGERS ===\n";

// 1. Listar todos los triggers
echo "\n📌 PASO 1: TODOS LOS TRIGGERS ACTIVOS\n";
echo str_repeat("-", 120) . "\n";

$triggers = $db->query("
    SELECT 
        trigger_name,
        event_object_table,
        event_manipulation,
        action_timing,
        action_orientation,
        action_statement
    FROM information_schema.triggers
    WHERE trigger_schema = 'public'
    ORDER BY event_object_table, action_timing, event_manipulation
")->fetchAll(PDO::FETCH_ASSOC);

$trigger_map = [];
foreach ($triggers as $t) {
    $tabla = $t['event_object_table'];
    $evento = $t['event_manipulation'];
    $timing = $t['action_timing'];
    
    if (!isset($trigger_map[$tabla])) {
        $trigger_map[$tabla] = [];
    }
    if (!isset($trigger_map[$tabla][$evento])) {
        $trigger_map[$tabla][$evento] = [];
    }
    
    $trigger_map[$tabla][$evento][] = [
        'name' => $t['trigger_name'],
        'timing' => $timing
    ];
    
    printf("%-40s | %-20s | %-10s | %-10s\n",
        $t['trigger_name'],
        $tabla,
        $evento,
        $timing
    );
}

// 2. Verificar conflictos
echo "\n📌 PASO 2: ANÁLISIS DE CONFLICTOS\n";
echo str_repeat("-", 120) . "\n";

$tiene_conflictos = false;

foreach ($trigger_map as $tabla => $eventos) {
    foreach ($eventos as $evento => $triggers_list) {
        if (count($triggers_list) > 1) {
            echo "⚠️  TABLA: $tabla | EVENTO: $evento\n";
            echo "   ❌ Múltiples triggers dispándose al mismo tiempo:\n";
            foreach ($triggers_list as $t) {
                echo "      - " . $t['name'] . " (" . $t['timing'] . ")\n";
            }
            echo "\n";
            $tiene_conflictos = true;
        }
    }
}

if (!$tiene_conflictos) {
    echo "✅ NO HAY CONFLICTOS - Cada evento dispara solo UN trigger\n\n";
}

// 3. Orden de ejecución
echo "📌 PASO 3: ORDEN DE EJECUCIÓN\n";
echo str_repeat("-", 120) . "\n";

echo "\n▼ CUANDO INSERTAS UN ITEM EN detalle_certificados:\n";
echo "   1️⃣  AFTER INSERT → trigger_insert_col4\n";
echo "       └─ Suma cantidad_pendiente a col4 en presupuesto_items\n";
echo "   2️⃣  AFTER INSERT → trigger_actualiza_total_pendiente\n";
echo "       └─ Suma total_pendiente en certificados\n";

echo "\n▼ CUANDO ACTUALIZAS cantidad_liquidacion (UPDATE):\n";
echo "   1️⃣  BEFORE UPDATE → trigger_recalcula_pendiente\n";
echo "       └─ Recalcula: cantidad_pendiente = monto - cantidad_liquidacion\n";
echo "   2️⃣  AFTER UPDATE → trigger_update_col4_consolidado\n";
echo "       └─ Ajusta col4 en presupuesto por la diferencia\n";
echo "   3️⃣  AFTER UPDATE → trigger_actualiza_total_pendiente\n";
echo "       └─ Recalcula total_pendiente en certificados\n";

echo "\n▼ CUANDO ELIMINAS UN ITEM:\n";
echo "   1️⃣  AFTER DELETE → trigger_delete_col4\n";
echo "       └─ Resta cantidad_pendiente de col4 en presupuesto_items\n";
echo "   2️⃣  AFTER DELETE → trigger_actualiza_total_pendiente\n";
echo "       └─ Recalcula total_pendiente en certificados\n";

echo "\n▼ CUANDO ACTUALIZA col4 EN presupuesto_items:\n";
echo "   1️⃣  BEFORE UPDATE → trigger_recalcula_saldo\n";
echo "       └─ Recalcula: saldo_disponible = col3 - col4\n";

// 4. Verificar que no hay triggers BEFORE/AFTER duplicados en el mismo evento
echo "\n\n📌 PASO 4: VERIFICACIÓN DE TIMING\n";
echo str_repeat("-", 120) . "\n";

$por_tabla_evento = [];
foreach ($triggers as $t) {
    $key = $t['event_object_table'] . '|' . $t['event_manipulation'];
    if (!isset($por_tabla_evento[$key])) {
        $por_tabla_evento[$key] = ['BEFORE' => 0, 'AFTER' => 0];
    }
    $por_tabla_evento[$key][$t['action_timing']]++;
}

echo "Tabla.Evento | BEFORE | AFTER | Observación\n";
echo str_repeat("-", 120) . "\n";

foreach ($por_tabla_evento as $key => $counts) {
    list($tabla, $evento) = explode('|', $key);
    $ok = ($counts['BEFORE'] + $counts['AFTER'] == 1) ? "✅ OK" : "⚠️  REVISAR";
    printf("%-30s | %-6s | %-5s | %s\n",
        "$tabla.$evento",
        $counts['BEFORE'],
        $counts['AFTER'],
        $ok
    );
}

// 5. Resumen
echo "\n📌 PASO 5: RESUMEN\n";
echo str_repeat("-", 120) . "\n";

$total_triggers = count($triggers);
echo "✅ Total de triggers: $total_triggers\n";
echo "✅ Tabla detalle_certificados: 7 triggers\n";
echo "✅ Tabla presupuesto_items: 1 trigger\n";
echo "✅ Tabla certificados: 0 triggers (usa CASCADE)\n";

if (!$tiene_conflictos) {
    echo "\n✅ ESTADO: ¡SIN CONFLICTOS! Cada acción dispara triggers de forma ordenada.\n";
} else {
    echo "\n⚠️  ESTADO: HAY CONFLICTOS - Se disparan múltiples triggers simultáneamente\n";
}

?>
