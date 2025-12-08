<?php
/**
 * Script para limpiar triggers y funciones antiguas
 * Ejecuta primero los triggers antiguos, luego las funciones
 */

try {
    $conn = new PDO('mysql:host=localhost;dbname=certificados_ueb', 'root', '');
    
    echo "=== LIMPIANDO TRIGGERS Y FUNCIONES ANTIGUAS ===\n\n";
    
    // Lista de triggers antiguos a eliminar
    $triggersToDelete = [
        'trg_sync_col4_on_insert',
        'trg_sync_col4_on_update',
        'trg_sync_col4_on_delete',
        'trigger_insert_detalle_certificados',
        'trigger_update_liquidacion',
        'trigger_recalculate_saldo_disponible',
        'trigger_delete_detalle_certificados',
    ];
    
    foreach ($triggersToDelete as $triggerName) {
        try {
            $conn->exec("DROP TRIGGER IF EXISTS $triggerName");
            echo "✅ Eliminado trigger: $triggerName\n";
        } catch (Exception $e) {
            echo "⚠️  No se pudo eliminar trigger $triggerName: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n--- Eliminando funciones antiguas ---\n\n";
    
    // Lista de funciones antiguas a eliminar
    $functionsToDelete = [
        'trg_sync_col4_on_insert',
        'trg_sync_col4_on_update',
        'trg_sync_col4_on_delete',
        'trigger_insert_detalle_certificados',
        'trigger_update_liquidacion',
        'trigger_recalculate_saldo_disponible',
        'trigger_delete_detalle_certificados',
    ];
    
    foreach ($functionsToDelete as $funcName) {
        try {
            $conn->exec("DROP FUNCTION IF EXISTS $funcName()");
            echo "✅ Eliminada función: $funcName()\n";
        } catch (Exception $e) {
            echo "⚠️  No se pudo eliminar función $funcName(): " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== VERIFICANDO TRIGGERS RESTANTES ===\n\n";
    
    $result = $conn->query("SELECT trigger_name, event_object_table FROM information_schema.triggers WHERE trigger_schema = 'certificados_ueb' ORDER BY event_object_table, trigger_name");
    $triggers = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($triggers)) {
        echo "❌ No hay triggers en la base de datos\n";
    } else {
        echo "✅ Triggers activos:\n\n";
        foreach ($triggers as $trigger) {
            echo "- " . $trigger['trigger_name'] . " (tabla: " . $trigger['event_object_table'] . ")\n";
        }
    }
    
    echo "\n✅ Limpieza completada\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
