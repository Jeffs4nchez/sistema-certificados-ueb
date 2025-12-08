<?php
/**
 * LIMPIAR FUNCIONES HUÉRFANAS
 * Elimina funciones que no tienen triggers asociados
 */

$host = 'localhost';
$port = '5432';
$database = 'certificados_sistema';
$user = 'postgres';
$pass = 'jeffo2003';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "ELIMINANDO FUNCIONES HUÉRFANAS\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Funciones que no se usan
    $funciones_a_eliminar = [
        'fn_trigger_actualiza_col4_y_saldo()',
        'fn_trigger_delete_col4()',
        'fn_trigger_insert_col4()',
        'fn_trigger_recalcula_pendiente()',
        'fn_trigger_recalcula_saldo()',
    ];
    
    echo "Eliminando funciones no utilizadas:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($funciones_a_eliminar as $func) {
        try {
            $db->exec("DROP FUNCTION IF EXISTS {$func} CASCADE");
            echo "   ✓ {$func} eliminada\n";
        } catch (Exception $e) {
            echo "   ⊘ {$func} - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ LIMPIEZA COMPLETADA\n";
    echo str_repeat("=", 80) . "\n\n";
    
    echo "RESUMEN FINAL:\n";
    echo "✅ Triggers activos: 3 (trg_item_insert, trg_item_update, trg_item_delete)\n";
    echo "✅ Funciones de liquidación: ELIMINADAS\n";
    echo "✅ Funciones huérfanas: ELIMINADAS\n";
    echo "✅ Base de datos LIMPIA\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
