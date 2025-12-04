<?php
$conn = pg_connect('host=localhost port=5432 dbname=certificados_sistema user=postgres password=jeffo2003');

if (!$conn) {
    die("Error de conexión\n");
}

echo "=== ELIMINANDO TRIGGERS VIEJOS Y CONFLICTIVOS ===\n\n";

$triggers_eliminar = [
    'trg_recalc_col4_after_cert_delete',
    'trg_sync_col4_delete',
    'trg_sync_col4_insert',
    'trg_sync_col4_update'
];

foreach ($triggers_eliminar as $trigger) {
    $result = pg_query($conn, "DROP TRIGGER IF EXISTS $trigger ON certificados CASCADE");
    if ($result) {
        echo "[✓] Eliminado trigger: $trigger\n";
    } else {
        echo "[✗] Error eliminando $trigger: " . pg_last_error($conn) . "\n";
    }
}

echo "\n=== ELIMINANDO FUNCIONES VIEJAS ASOCIADAS ===\n\n";

$functions_eliminar = [
    'trg_recalc_col4_after_cert_delete()',
    'trg_sync_col4_delete()',
    'trg_sync_col4_insert()',
    'trg_sync_col4_update()',
    'sync_col4_from_certificates()',
    'sync_presupuesto_on_delete()',
    'sync_presupuesto_on_insert()',
    'sync_presupuesto_on_update()',
    'sync_col4_after_delete()',
    'recalc_col4_after_cert_delete()',
];

foreach ($functions_eliminar as $func) {
    $result = pg_query($conn, "DROP FUNCTION IF EXISTS $func CASCADE");
    if ($result) {
        echo "[✓] Eliminada función: $func\n";
    } else {
        echo "[✗] Error eliminando $func\n";
    }
}

echo "\n=== VERIFICANDO TRIGGERS RESTANTES ===\n\n";

$result = pg_query($conn, "
    SELECT trigger_name, event_object_table
    FROM information_schema.triggers
    WHERE trigger_schema = 'public'
    ORDER BY event_object_table, trigger_name
");

while ($row = pg_fetch_assoc($result)) {
    echo "- {$row['trigger_name']} en tabla {$row['event_object_table']}\n";
}

pg_close($conn);
echo "\n✓ Limpieza completada\n";
?>
