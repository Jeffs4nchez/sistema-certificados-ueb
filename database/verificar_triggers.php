<?php
$conn = pg_connect('host=localhost port=5432 dbname=certificados_sistema user=postgres password=jeffo2003');

if (!$conn) {
    die("Error de conexión\n");
}

// Obtener todos los triggers
$result = pg_query($conn, "
    SELECT trigger_name, event_object_table, event_manipulation
    FROM information_schema.triggers
    WHERE trigger_schema = 'public'
    ORDER BY event_object_table, trigger_name
");

echo "=== TRIGGERS ACTUALES ===\n";
$triggers = [];
while ($row = pg_fetch_assoc($result)) {
    $triggers[] = $row;
    echo "- {$row['trigger_name']} ({$row['event_manipulation']}) en tabla {$row['event_object_table']}\n";
}

echo "\nTotal: " . count($triggers) . " triggers\n";

pg_close($conn);
?>
