<?php
$conn = pg_connect('host=localhost port=5432 dbname=certificados_sistema user=postgres password=jeffo2003');

if (!$conn) {
    die("Error de conexión\n");
}

echo "=== ELIMINANDO FUNCIONES VIEJAS ===\n\n";

$functions = [
    'trg_sync_col4_on_insert()',
    'trg_sync_col4_on_update()',
    'trg_sync_col4_on_delete()'
];

foreach ($functions as $func) {
    $result = pg_query($conn, "DROP FUNCTION IF EXISTS $func CASCADE");
    if ($result) {
        echo "[✓] Eliminada función: $func\n";
    } else {
        echo "[✗] Error: " . pg_last_error($conn) . "\n";
    }
}

pg_close($conn);
?>
