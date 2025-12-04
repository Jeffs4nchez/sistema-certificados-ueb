<?php
$conn = pg_connect('host=localhost port=5432 dbname=certificados_sistema user=postgres password=jeffo2003');

if (!$conn) {
    die("Error de conexión\n");
}

echo "=== REVISANDO FUNCIÓN DEL TRIGGER col4_on_insert ===\n\n";

$result = pg_query($conn, "
    SELECT routine_definition
    FROM information_schema.routines
    WHERE routine_name = 'trg_sync_col4_on_insert'
    AND routine_schema = 'public'
");

if ($row = pg_fetch_assoc($result)) {
    echo $row['routine_definition'] . "\n";
} else {
    echo "No encontrada función trg_sync_col4_on_insert\n";
}

pg_close($conn);
?>
