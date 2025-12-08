<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════╗\n";
echo "║  Verificar Triggers Activos en PostgreSQL    ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

try {
    // Obtener todos los triggers
    $stmt = $db->query("
        SELECT trigger_name, event_object_table, event_manipulation
        FROM information_schema.triggers
        WHERE trigger_schema NOT IN ('pg_catalog', 'information_schema')
        ORDER BY event_object_table, trigger_name
    ");
    
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($triggers)) {
        echo "✓ " . count($triggers) . " triggers encontrados:\n\n";
        foreach ($triggers as $trigger) {
            echo "  Trigger: {$trigger['trigger_name']}\n";
            echo "    Tabla: {$trigger['event_object_table']}\n";
            echo "    Evento: {$trigger['event_manipulation']}\n\n";
        }
    } else {
        echo "❌ No se encontraron triggers en la base de datos\n";
    }
    
    // Ahora buscar específicamente nuestros triggers
    echo "\n╔════════════════════════════════════════════════╗\n";
    echo "║  Verificar Nuestros Triggers Específicos     ║\n";
    echo "╚════════════════════════════════════════════════╝\n\n";
    
    $triggerNames = [
        'trigger_certificados_actualiza_col4',
        'trigger_detalle_insert_col4',
        'trigger_detalle_update_col4',
        'trigger_detalle_delete_col4',
        'trigger_col4_recalcula_saldo'
    ];
    
    foreach ($triggerNames as $name) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as cnt
            FROM information_schema.triggers
            WHERE trigger_name = ?
        ");
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['cnt'] > 0) {
            echo "✓ $name: ACTIVO\n";
        } else {
            echo "❌ $name: NO ENCONTRADO\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
