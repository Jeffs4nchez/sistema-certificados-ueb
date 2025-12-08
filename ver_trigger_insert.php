<?php
/**
 * Ver qué hace el trigger trg_item_insert actualmente
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "VER CÓDIGO DEL TRIGGER trg_item_insert\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Ver el código del trigger
    $stmt = $db->query("
        SELECT trigger_name, action_statement
        FROM information_schema.triggers
        WHERE trigger_name = 'trg_item_insert'
    ");
    
    $trigger = $stmt->fetch();
    
    if (!$trigger) {
        echo "❌ Trigger 'trg_item_insert' no encontrado\n\n";
        
        // Listar todos los triggers
        echo "Triggers disponibles:\n";
        $stmt = $db->query("
            SELECT trigger_name, event_manipulation, action_timing
            FROM information_schema.triggers
            WHERE event_object_table = 'detalle_certificados'
        ");
        
        $triggers = $stmt->fetchAll();
        foreach ($triggers as $t) {
            echo "  - {$t['trigger_name']} ({$t['action_timing']} {$t['event_manipulation']})\n";
        }
        exit(0);
    }
    
    echo "Trigger: {$trigger['trigger_name']}\n\n";
    echo "Código SQL:\n";
    echo str_repeat("-", 80) . "\n";
    echo $trigger['action_statement'] . "\n";
    echo str_repeat("-", 80) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
