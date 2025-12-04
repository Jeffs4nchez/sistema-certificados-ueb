<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== REVISANDO TRIGGERS INSERT ===\n\n";

// Ver todos los triggers en detalle_certificados
$query = "SELECT trigger_name, event_manipulation, action_timing
FROM information_schema.triggers 
WHERE trigger_schema = 'public'
AND event_object_table = 'detalle_certificados'
ORDER BY event_manipulation, trigger_name";

$stmt = $conn->query($query);
$triggers = $stmt->fetchAll();

echo "Triggers en detalle_certificados:\n";
echo "=================================\n\n";

$insertTriggers = [];
foreach($triggers as $trigger) {
    echo "- {$trigger['trigger_name']} ({$trigger['action_timing']} {$trigger['event_manipulation']})\n";
    if($trigger['event_manipulation'] == 'INSERT') {
        $insertTriggers[] = $trigger['trigger_name'];
    }
}

echo "\n\nTriggers INSERT que se ejecutan:\n";
foreach($insertTriggers as $name) {
    echo "- $name\n";
}

if(count($insertTriggers) > 1) {
    echo "\n⚠️  PROBLEMA: Hay " . count($insertTriggers) . " triggers INSERT\n";
    echo "Esto puede causar que col4 se actualice MÚLTIPLES VECES\n\n";
    
    // Ver qué hace cada uno
    echo "\n=== DETALLES DE FUNCIONES ===\n\n";
    
    foreach($insertTriggers as $name) {
        $funcQuery = "SELECT routine_definition FROM information_schema.routines 
        WHERE routine_type = 'FUNCTION'
        AND routine_schema = 'public'
        AND routine_name = SUBSTRING(?, 1, POSITION('_' IN ?) - 1) || '%'";
        
        // Obtener el nombre de la función de cada trigger
        $triggerDetailsQuery = "SELECT trigger_name FROM information_schema.triggers 
        WHERE trigger_schema = 'public' AND trigger_name = ?";
        
        $trigStmt = $conn->prepare($triggerDetailsQuery);
        $trigStmt->execute([$name]);
        $trigDetail = $trigStmt->fetch();
        
        echo "Trigger: $name\n";
        echo "Función asociada: " . (strpos($name, 'trigger_insert') === 0 ? 'trigger_insert_detalle_certificados' : 'sync_col4_from_certificates') . "\n";
        echo "---\n\n";
    }
}

echo "\n\n=== CONCLUSIÓN ===\n";
if(count($insertTriggers) > 1) {
    echo "❌ ENCONTRADO: Múltiples triggers INSERT\n";
    echo "Triggers a mantener:\n";
    echo "  ✓ trigger_insert_detalle_certificados (actualiza col4 y col8)\n";
    echo "  ✓ trigger_insert_certificado_liquidacion (actualiza certificados)\n";
    echo "  ✓ sync_col4_from_certificates (actualiza col4 desde total_pendiente)\n\n";
    echo "ESPERA: El problema es que:\n";
    echo "1. trigger_insert_detalle_certificados SUMA col4\n";
    echo "2. sync_col4_from_certificates REEMPLAZA col4 con total_pendiente\n";
    echo "3. Si se ejecutan en orden, puede quedar incorrecto\n";
}
?>
