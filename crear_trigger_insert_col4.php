<?php
/**
 * CORREGIR: Crear trigger para restar col4 en INSERT también
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "CORREGIR: Crear trigger para restar col4 en INSERT\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Crear trigger para INSERT
    echo "1️⃣  CREAR TRIGGER AFTER INSERT\n";
    echo str_repeat("-", 80) . "\n";
    
    $trigger_insert = "
        DROP TRIGGER IF EXISTS trg_col4_por_liquidacion_insert ON detalle_certificados CASCADE;
        
        CREATE TRIGGER trg_col4_por_liquidacion_insert
        AFTER INSERT ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_actualizar_col4_por_liquidacion();
    ";
    
    $db->exec($trigger_insert);
    echo "✅ Trigger creado: trg_col4_por_liquidacion_insert\n\n";
    
    // Verificar
    echo "2️⃣  VERIFICAR TRIGGERS\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT trigger_name, event_manipulation
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        AND trigger_name LIKE 'trg_col4%'
        ORDER BY trigger_name
    ");
    
    $triggers = $stmt->fetchAll();
    foreach ($triggers as $trg) {
        echo "✅ {$trg['trigger_name']} (AFTER {$trg['event_manipulation']})\n";
    }
    
    echo "\n✅ TRIGGER AGREGADO\n";
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
