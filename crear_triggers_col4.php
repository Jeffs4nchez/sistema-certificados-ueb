<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== CREANDO TRIGGERS PARA col4 = total_pendiente ===\n\n";

// Trigger 1: Cuando se INSERT certificado o se actualiza total_pendiente
$trigger1 = "
CREATE OR REPLACE FUNCTION sync_col4_from_total_pendiente()
RETURNS TRIGGER AS \$\$
BEGIN
    -- Actualizar presupuesto_items.col4 con el total_pendiente
    -- para todos los items que este certificado utiliza
    UPDATE presupuesto_items
    SET col4 = (
        SELECT COALESCE(SUM(total_pendiente), 0)
        FROM certificados
        WHERE detalle_certificados.codigo_completo IN (
            SELECT codigo_completo FROM detalle_certificados 
            WHERE certificado_id = NEW.id
        )
    ),
    col8 = (SELECT col1 FROM presupuesto_items pi WHERE pi.id = presupuesto_items.id) 
        - (SELECT COALESCE(SUM(total_pendiente), 0)
           FROM certificados
           WHERE id IN (SELECT certificado_id FROM detalle_certificados WHERE codigo_completo = presupuesto_items.codigo_completo)),
    fecha_actualizacion = NOW()
    WHERE codigo_completo IN (
        SELECT codigo_completo FROM detalle_certificados WHERE certificado_id = NEW.id
    );
    
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

// Trigger 2: Cuando se UPDATE un certificado (cambio en total_pendiente)
$trigger2 = "
DROP TRIGGER IF EXISTS trg_sync_col4_on_cert_update ON certificados CASCADE;
CREATE TRIGGER trg_sync_col4_on_cert_update
AFTER UPDATE ON certificados
FOR EACH ROW
WHEN (OLD.total_pendiente IS DISTINCT FROM NEW.total_pendiente)
EXECUTE FUNCTION sync_col4_from_total_pendiente();
";

// Trigger 3: Cuando se INSERT certificado
$trigger3 = "
DROP TRIGGER IF EXISTS trg_sync_col4_on_cert_insert ON certificados CASCADE;
CREATE TRIGGER trg_sync_col4_on_cert_insert
AFTER INSERT ON certificados
FOR EACH ROW
EXECUTE FUNCTION sync_col4_from_total_pendiente();
";

// Trigger 4: Cuando se DELETE certificado
$trigger4 = "
CREATE OR REPLACE FUNCTION sync_col4_on_cert_delete()
RETURNS TRIGGER AS \$\$
BEGIN
    -- Actualizar presupuesto_items.col4 cuando se elimina un certificado
    UPDATE presupuesto_items
    SET col4 = (
        SELECT COALESCE(SUM(total_pendiente), 0)
        FROM certificados
        WHERE codigo_completo IN (
            SELECT codigo_completo FROM detalle_certificados 
            WHERE certificado_id = OLD.id
        )
    ),
    col8 = (SELECT col1 FROM presupuesto_items pi WHERE pi.id = presupuesto_items.id) 
        - (SELECT COALESCE(SUM(total_pendiente), 0)
           FROM certificados
           WHERE id IN (SELECT certificado_id FROM detalle_certificados WHERE codigo_completo = presupuesto_items.codigo_completo)),
    fecha_actualizacion = NOW()
    WHERE codigo_completo IN (
        SELECT codigo_completo FROM detalle_certificados WHERE certificado_id = OLD.id
    );
    
    RETURN OLD;
END;
\$\$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_sync_col4_on_cert_delete ON certificados CASCADE;
CREATE TRIGGER trg_sync_col4_on_cert_delete
BEFORE DELETE ON certificados
FOR EACH ROW
EXECUTE FUNCTION sync_col4_on_cert_delete();
";

// Ejecutar triggers
try {
    echo "1️⃣  Creando función sync_col4_from_total_pendiente()...\n";
    $conn->exec($trigger1);
    echo "✓ Función creada\n\n";
    
    echo "2️⃣  Creando trigger en INSERT certificados...\n";
    $conn->exec($trigger3);
    echo "✓ Trigger INSERT creado\n\n";
    
    echo "3️⃣  Creando trigger en UPDATE certificados...\n";
    $conn->exec($trigger2);
    echo "✓ Trigger UPDATE creado\n\n";
    
    echo "4️⃣  Creando trigger en DELETE certificados...\n";
    $conn->exec($trigger4);
    echo "✓ Trigger DELETE creado\n\n";
    
    echo "✅ TODOS LOS TRIGGERS CREADOS EXITOSAMENTE\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n\n=== VERIFICANDO TRIGGERS ===\n\n";

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
