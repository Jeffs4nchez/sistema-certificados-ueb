<?php
/**
 * ELIMINAR TODAS LAS FUNCIONES TRIGGER
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "ELIMINAR TODAS LAS FUNCIONES TRIGGER\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Listar todas las funciones trigger
    echo "1️⃣  FUNCIONES A ELIMINAR\n";
    echo str_repeat("-", 100) . "\n";
    
    $functions = [
        'fn_actualiza_total_pendiente',
        'fn_actualizar_col4_delete',
        'fn_actualizar_col4_insert',
        'fn_actualizar_col4_update',
        'fn_detalle_before_delete',
        'fn_detalle_before_update',
        'fn_detalle_insert_update',
        'fn_restar_pendiente_col4',
        'fn_trigger_detalle_cantidad_pendiente',
        'fn_trigger_item_delete',
        'fn_trigger_item_insert',
        'fn_trigger_item_update',
        'trigger_update_certificados_totales',
        'trigger_update_certificados_totales_delete'
    ];
    
    foreach ($functions as $func) {
        echo "  - $func\n";
    }
    
    echo "\n";
    
    // 2. Eliminar todas las funciones
    echo "2️⃣  ELIMINANDO FUNCIONES\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($functions as $func) {
        $sql = "DROP FUNCTION IF EXISTS $func() CASCADE";
        try {
            $db->exec($sql);
            echo "✅ $func eliminada\n";
        } catch (Exception $e) {
            // Si falla, intentar sin CASCADE
            try {
                $db->exec("DROP FUNCTION IF EXISTS $func()");
                echo "✅ $func eliminada\n";
            } catch (Exception $e2) {
                echo "⚠️  $func no se pudo eliminar (puede no existir)\n";
            }
        }
    }
    
    echo "\n";
    
    // 3. Verificar que no hay funciones trigger
    echo "3️⃣  VERIFICACIÓN FINAL\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT routine_name
        FROM information_schema.routines
        WHERE routine_schema = 'public' 
        AND routine_type = 'FUNCTION'
        AND (routine_name LIKE 'fn_%' OR routine_name LIKE 'trigger_%')
        ORDER BY routine_name
    ");
    
    $remaining = $stmt->fetchAll();
    
    if (count($remaining) == 0) {
        echo "✅✅✅ TODAS LAS FUNCIONES TRIGGER HAN SIDO ELIMINADAS\n";
    } else {
        echo "⚠️  Aún hay " . count($remaining) . " funciones:\n";
        foreach ($remaining as $func) {
            echo "  - {$func['routine_name']}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
