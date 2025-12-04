<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== CORRIGIENDO TRIGGERS ===\n\n";

// Eliminar los triggers incorrectos
$dropTriggers = [
    "DROP TRIGGER IF EXISTS trg_sync_col4_on_cert_insert ON certificados CASCADE",
    "DROP TRIGGER IF EXISTS trg_sync_col4_on_cert_update ON certificados CASCADE",
    "DROP TRIGGER IF EXISTS trg_sync_col4_on_cert_delete ON certificados CASCADE",
    "DROP FUNCTION IF EXISTS sync_col4_from_total_pendiente() CASCADE",
    "DROP FUNCTION IF EXISTS sync_col4_on_cert_delete() CASCADE"
];

foreach($dropTriggers as $sql) {
    try {
        $conn->exec($sql);
        echo "✓ " . substr($sql, 5, 50) . "...\n";
    } catch (Exception $e) {
        echo "- Saltado: " . substr($sql, 5, 50) . "\n";
    }
}

echo "\n=== CREANDO NUEVOS TRIGGERS CORRECTOS ===\n\n";

// Trigger mejorado para INSERT
$trigger1 = "
CREATE OR REPLACE FUNCTION sync_col4_from_certificates()
RETURNS TRIGGER AS \$\$
BEGIN
    -- Actualizar presupuesto_items.col4 basado en total_pendiente de certificados
    -- que utilizan cada código_completo
    UPDATE presupuesto_items
    SET col4 = (
        SELECT COALESCE(SUM(c.total_pendiente), 0)
        FROM certificados c
        INNER JOIN detalle_certificados dc ON c.id = dc.certificado_id
        WHERE dc.codigo_completo = presupuesto_items.codigo_completo
    ),
    col8 = (
        SELECT col1 FROM presupuesto_items pi WHERE pi.id = presupuesto_items.id
    ) - (
        SELECT COALESCE(SUM(c.total_pendiente), 0)
        FROM certificados c
        INNER JOIN detalle_certificados dc ON c.id = dc.certificado_id
        WHERE dc.codigo_completo = presupuesto_items.codigo_completo
    ),
    fecha_actualizacion = NOW()
    WHERE codigo_completo IN (
        SELECT DISTINCT dc.codigo_completo 
        FROM detalle_certificados dc 
        WHERE dc.certificado_id = NEW.id
    );
    
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

// Trigger para DELETE
$trigger2 = "
CREATE OR REPLACE FUNCTION sync_col4_on_cert_delete_fixed()
RETURNS TRIGGER AS \$\$
BEGIN
    UPDATE presupuesto_items
    SET col4 = (
        SELECT COALESCE(SUM(c.total_pendiente), 0)
        FROM certificados c
        INNER JOIN detalle_certificados dc ON c.id = dc.certificado_id
        WHERE dc.codigo_completo = presupuesto_items.codigo_completo
    ),
    col8 = (
        SELECT col1 FROM presupuesto_items pi WHERE pi.id = presupuesto_items.id
    ) - (
        SELECT COALESCE(SUM(c.total_pendiente), 0)
        FROM certificados c
        INNER JOIN detalle_certificados dc ON c.id = dc.certificado_id
        WHERE dc.codigo_completo = presupuesto_items.codigo_completo
    ),
    fecha_actualizacion = NOW()
    WHERE codigo_completo IN (
        SELECT DISTINCT dc.codigo_completo 
        FROM detalle_certificados dc 
        WHERE dc.certificado_id = OLD.id
    );
    
    RETURN OLD;
END;
\$\$ LANGUAGE plpgsql;
";

// Crear triggers
try {
    echo "1️⃣  Creando función sync_col4_from_certificates()...\n";
    $conn->exec($trigger1);
    echo "✓ Función creada\n\n";
    
    echo "2️⃣  Creando trigger en INSERT certificados...\n";
    $conn->exec("DROP TRIGGER IF EXISTS trg_sync_col4_insert ON certificados CASCADE");
    $conn->exec("CREATE TRIGGER trg_sync_col4_insert
    AFTER INSERT ON certificados
    FOR EACH ROW
    EXECUTE FUNCTION sync_col4_from_certificates()");
    echo "✓ Trigger INSERT creado\n\n";
    
    echo "3️⃣  Creando trigger en UPDATE certificados (cuando cambia total_pendiente)...\n";
    $conn->exec("DROP TRIGGER IF EXISTS trg_sync_col4_update ON certificados CASCADE");
    $conn->exec("CREATE TRIGGER trg_sync_col4_update
    AFTER UPDATE ON certificados
    FOR EACH ROW
    WHEN (OLD.total_pendiente IS DISTINCT FROM NEW.total_pendiente)
    EXECUTE FUNCTION sync_col4_from_certificates()");
    echo "✓ Trigger UPDATE creado\n\n";
    
    echo "4️⃣  Creando función sync_col4_on_cert_delete_fixed()...\n";
    $conn->exec($trigger2);
    echo "✓ Función creada\n\n";
    
    echo "5️⃣  Creando trigger en DELETE certificados...\n";
    $conn->exec("DROP TRIGGER IF EXISTS trg_sync_col4_delete ON certificados CASCADE");
    $conn->exec("CREATE TRIGGER trg_sync_col4_delete
    BEFORE DELETE ON certificados
    FOR EACH ROW
    EXECUTE FUNCTION sync_col4_on_cert_delete_fixed()");
    echo "✓ Trigger DELETE creado\n\n";
    
    echo "✅ TODOS LOS TRIGGERS CORREGIDOS\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// Verificar
echo "\n\n=== VERIFICANDO ===\n\n";
$query = "SELECT trigger_name, event_manipulation 
FROM information_schema.triggers 
WHERE trigger_schema = 'public'
AND trigger_name LIKE 'trg_sync%'
ORDER BY trigger_name";

$stmt = $conn->query($query);
$triggers = $stmt->fetchAll();

foreach($triggers as $trigger) {
    echo "✓ {$trigger['trigger_name']} ({$trigger['event_manipulation']})\n";
}
?>
