<?php
$conn = pg_connect('host=localhost port=5432 dbname=certificados_sistema user=postgres password=jeffo2003');

if (!$conn) {
    die("Error de conexión\n");
}

echo "=== RECREANDO TRIGGERS DE COL4 CON SUMA CORRECTA ===\n\n";

// Primero eliminar los viejos
$triggers = ['trg_sync_col4_on_insert', 'trg_sync_col4_on_update', 'trg_sync_col4_on_delete'];
foreach ($triggers as $trigger) {
    pg_query($conn, "DROP TRIGGER IF EXISTS $trigger ON detalle_certificados CASCADE");
    echo "[✓] Eliminado trigger: $trigger\n";
}

// Crear función para INSERT
$insert_function = "
CREATE FUNCTION trg_sync_col4_on_insert()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = (
            SELECT SUM(monto)
            FROM detalle_certificados
            WHERE codigo_completo = NEW.codigo_completo
        ),
        col8 = COALESCE(col1, 0) - (
            SELECT SUM(monto)
            FROM detalle_certificados
            WHERE codigo_completo = NEW.codigo_completo
        ),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

// Crear función para UPDATE
$update_function = "
CREATE FUNCTION trg_sync_col4_on_update()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = (
            SELECT SUM(monto)
            FROM detalle_certificados
            WHERE codigo_completo = NEW.codigo_completo
        ),
        col8 = COALESCE(col1, 0) - (
            SELECT SUM(monto)
            FROM detalle_certificados
            WHERE codigo_completo = NEW.codigo_completo
        ),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

// Crear función para DELETE
$delete_function = "
CREATE FUNCTION trg_sync_col4_on_delete()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = COALESCE((
            SELECT SUM(monto)
            FROM detalle_certificados
            WHERE codigo_completo = OLD.codigo_completo
        ), 0),
        col8 = COALESCE(col1, 0) - COALESCE((
            SELECT SUM(monto)
            FROM detalle_certificados
            WHERE codigo_completo = OLD.codigo_completo
        ), 0),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = OLD.codigo_completo;
    RETURN OLD;
END;
\$\$ LANGUAGE plpgsql;
";

// Ejecutar creación de funciones
if (pg_query($conn, $insert_function)) {
    echo "[✓] Función trg_sync_col4_on_insert() creada con SUM()\n";
} else {
    echo "[✗] Error creando trg_sync_col4_on_insert: " . pg_last_error($conn) . "\n";
}

if (pg_query($conn, $update_function)) {
    echo "[✓] Función trg_sync_col4_on_update() creada con SUM()\n";
} else {
    echo "[✗] Error creando trg_sync_col4_on_update: " . pg_last_error($conn) . "\n";
}

if (pg_query($conn, $delete_function)) {
    echo "[✓] Función trg_sync_col4_on_delete() creada con SUM()\n";
} else {
    echo "[✗] Error creando trg_sync_col4_on_delete: " . pg_last_error($conn) . "\n";
}

// Crear triggers
echo "\n=== CREANDO TRIGGERS ===\n\n";

$triggers_sql = [
    "CREATE TRIGGER trg_sync_col4_on_insert AFTER INSERT ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_sync_col4_on_insert();" => "INSERT",
    "CREATE TRIGGER trg_sync_col4_on_update AFTER UPDATE ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_sync_col4_on_update();" => "UPDATE",
    "CREATE TRIGGER trg_sync_col4_on_delete AFTER DELETE ON detalle_certificados FOR EACH ROW EXECUTE FUNCTION trg_sync_col4_on_delete();" => "DELETE"
];

foreach ($triggers_sql as $sql => $type) {
    if (pg_query($conn, $sql)) {
        echo "[✓] Trigger para $type creado\n";
    } else {
        echo "[✗] Error creando trigger para $type: " . pg_last_error($conn) . "\n";
    }
}

echo "\n=== VERIFICANDO TRIGGERS FINALES ===\n\n";

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
echo "\n✓ Triggers de col4 corregidos con SUM() por codigo_completo\n";
?>
