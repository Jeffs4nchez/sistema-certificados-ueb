<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== LIMPIANDO TRIGGERS DUPLICADOS ===\n";

// Eliminar los triggers antiguos que duplican la lógica
$eliminar = [
    "DROP TRIGGER IF EXISTS trigger_insert_col4 ON detalle_certificados;",
    "DROP TRIGGER IF EXISTS trigger_update_col4 ON detalle_certificados;",
    "DROP TRIGGER IF EXISTS trigger_delete_col4 ON detalle_certificados;",
    "DROP FUNCTION IF EXISTS fn_trigger_insert_col4();",
    "DROP FUNCTION IF EXISTS fn_trigger_update_col4();",
    "DROP FUNCTION IF EXISTS fn_trigger_delete_col4();"
];

echo "📌 Eliminando triggers antiguos duplicados...\n";
foreach ($eliminar as $sql) {
    try {
        $db->exec($sql);
        echo "✅ " . substr($sql, 0, 50) . "\n";
    } catch (Exception $e) {
        echo "⚠️  " . $e->getMessage() . "\n";
    }
}

echo "\n📌 Triggers activos restantes:\n";
$triggers = $db->query("
    SELECT trigger_name, event_manipulation
    FROM information_schema.triggers
    WHERE trigger_schema = 'public'
    ORDER BY trigger_name
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($triggers as $t) {
    echo "   ✅ " . $t['trigger_name'] . " (" . $t['event_manipulation'] . ")\n";
}

echo "\n✅ Limpieza completada.\n";
?>
