<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== ANALIZANDO FLUJO DE LIQUIDACION ===\n";

// 1. Ver todos los triggers activos
echo "\n📌 TRIGGERS ACTIVOS:\n";
$triggers = $db->query("
    SELECT trigger_name, event_object_table, event_manipulation
    FROM information_schema.triggers
    WHERE trigger_schema = 'public'
    ORDER BY event_object_table, trigger_name
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($triggers as $t) {
    echo "   - " . $t['trigger_name'] . " (" . $t['event_manipulation'] . " ON " . $t['event_object_table'] . ")\n";
}

// 2. Ver funciones activas
echo "\n📌 FUNCIONES ACTIVAS:\n";
$functions = $db->query("
    SELECT routine_name
    FROM information_schema.routines
    WHERE routine_schema = 'public' AND routine_type = 'FUNCTION'
    ORDER BY routine_name
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($functions as $f) {
    echo "   - " . $f['routine_name'] . "()\n";
}

// 3. Ver código de cada función
echo "\n📌 CODIGO DE FUNCIONES:\n";
foreach ($functions as $f) {
    $code = $db->query("SELECT pg_get_functiondef('" . $f['routine_name'] . "'::regprocedure) as def")->fetch()['def'] ?? '';
    if (strpos($code, 'trigger') !== false || strpos($code, 'UPDATE') !== false) {
        echo "\n▼▼▼ " . $f['routine_name'] . "() ▼▼▼\n";
        echo str_repeat("-", 100) . "\n";
        echo substr($code, 0, 500) . "...\n";
        echo str_repeat("-", 100) . "\n";
    }
}

// 4. Ver datos actuales
echo "\n📌 ESTADO ACTUAL DE DATOS:\n";
$items = $db->query("
    SELECT 
        dc.id, dc.certificado_id, dc.codigo_completo, dc.descripcion_item,
        dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
        pi.col4, pi.col3, pi.saldo_disponible
    FROM detalle_certificados dc
    LEFT JOIN presupuesto_items pi ON pi.codigo_completo = dc.codigo_completo
    ORDER BY dc.certificado_id DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

echo str_repeat("-", 180) . "\n";
printf("%-5s | %-5s | %-25s | %-10s | %-12s | %-12s | %-12s | %-12s\n",
    "ID", "CERT", "CODIGO", "MONTO", "LIQUIDADO", "PENDIENTE", "PRESUP_COL4", "ESTADO");
echo str_repeat("-", 180) . "\n";

foreach ($items as $row) {
    $ok = (abs($row['col4'] - $row['cantidad_pendiente']) < 0.01) ? "✅" : "❌";
    printf("%-5s | %-5s | %-25s | %-10.2f | %-12.2f | %-12.2f | %-12.2f | %s\n",
        $row['id'],
        $row['certificado_id'],
        substr($row['codigo_completo'], 0, 23),
        $row['monto'],
        $row['cantidad_liquidacion'] ?? 0,
        $row['cantidad_pendiente'],
        $row['col4'] ?? 'NULL',
        $ok
    );
}

echo "\n✅ Análisis completado.\n";
?>
