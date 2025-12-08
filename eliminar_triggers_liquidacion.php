<?php
/**
 * ELIMINAR TRIGGERS DE LIQUIDACIÓN Y USAR PHP PURO
 * 
 * Este script:
 * 1. Elimina todos los triggers relacionados con liquidación
 * 2. Deja solo los triggers de INSERT/UPDATE/DELETE básicos para col4
 * 3. Todo lo demás se maneja con PHP código puro
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
    echo "ELIMINANDO TRIGGERS DE LIQUIDACIÓN\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Lista de triggers relacionados con liquidación a eliminar
    $liquidacion_triggers = [
        'trigger_update_liquidacion',
        'trigger_update_liquidado_insert',
        'trigger_update_liquidado_update',
        'trigger_update_liquidado_delete',
        'trigger_liquidacion_actualiza_col7',
        'trg_update_liquidado_insert',
        'trg_update_liquidado_update',
        'trg_update_liquidado_delete',
    ];
    
    echo "🔍 Eliminando triggers de liquidación...\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($liquidacion_triggers as $trigger) {
        try {
            $db->exec("DROP TRIGGER IF EXISTS {$trigger} ON detalle_certificados CASCADE");
            echo "   ✓ {$trigger} eliminado\n";
        } catch (Exception $e) {
            // Silenciamos errores de triggers que no existen
        }
    }
    
    // Eliminar funciones relacionadas con liquidación
    echo "\n🔍 Eliminando funciones de liquidación...\n";
    echo str_repeat("-", 80) . "\n";
    
    $liquidacion_functions = [
        'trigger_update_liquidacion()',
        'trigger_update_liquidado_insert()',
        'trigger_update_liquidado_update()',
        'trigger_update_liquidado_delete()',
        'trigger_liquidacion_actualiza_col7()',
        'trg_update_liquidado_insert()',
        'trg_update_liquidado_update()',
        'trg_update_liquidado_delete()',
    ];
    
    foreach ($liquidacion_functions as $func) {
        try {
            $db->exec("DROP FUNCTION IF EXISTS {$func} CASCADE");
            echo "   ✓ {$func} eliminada\n";
        } catch (Exception $e) {
            // Silenciamos errores de funciones que no existen
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✅ TRIGGERS DE LIQUIDACIÓN ELIMINADOS\n";
    echo str_repeat("=", 80) . "\n\n";
    
    echo "PRÓXIMOS PASOS:\n";
    echo "1. Actualiza Certificate.php con la lógica de liquidación en PHP\n";
    echo "2. Ahora TODO se maneja con código PHP puro\n";
    echo "3. No hay más triggers complicados\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
