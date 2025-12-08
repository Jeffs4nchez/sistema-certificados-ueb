<?php
/**
 * ELIMINAR TODOS LOS TRIGGERS DE LA BASE DE DATOS
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "ELIMINAR TODOS LOS TRIGGERS\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Listar todos los triggers
    echo "1️⃣  TRIGGERS A ELIMINAR\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT trigger_name, event_object_table
        FROM information_schema.triggers
        WHERE trigger_schema = 'public'
        ORDER BY event_object_table, trigger_name
    ");
    
    $triggers = $stmt->fetchAll();
    
    if (count($triggers) == 0) {
        echo "✅ No hay triggers en la base de datos\n\n";
    } else {
        foreach ($triggers as $trg) {
            echo "  - {$trg['trigger_name']} (tabla: {$trg['event_object_table']})\n";
        }
        echo "\n";
    }
    
    // 2. Eliminar todos los triggers
    echo "2️⃣  ELIMINANDO TODOS LOS TRIGGERS\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($triggers as $trg) {
        $sql = "DROP TRIGGER IF EXISTS {$trg['trigger_name']} ON {$trg['event_object_table']} CASCADE";
        try {
            $db->exec($sql);
            echo "✅ {$trg['trigger_name']} eliminado\n";
        } catch (Exception $e) {
            echo "❌ Error al eliminar {$trg['trigger_name']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // 3. Verificar que no hay triggers
    echo "3️⃣  VERIFICACIÓN FINAL\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT COUNT(*) as cantidad
        FROM information_schema.triggers
        WHERE trigger_schema = 'public'
    ");
    
    $result = $stmt->fetch();
    $cantidad = $result['cantidad'];
    
    if ($cantidad == 0) {
        echo "✅✅✅ TODOS LOS TRIGGERS HAN SIDO ELIMINADOS\n";
        echo "\nLa base de datos ahora funciona sin triggers automáticos.\n";
        echo "Los cambios deben hacerse manualmente desde el código PHP.\n";
    } else {
        echo "⚠️  Aún hay $cantidad triggers en la base de datos\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
