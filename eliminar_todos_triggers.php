<?php
/**
 * Script para eliminar todos los triggers antiguos
 */

$host = 'localhost';
$port = '5432';
$user = 'postgres';
$pass = 'jeffo2003';
$database = 'certificados_sistema';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    
    echo "🗑️ ELIMINANDO TODOS LOS TRIGGERS...\n";
    echo str_repeat("=", 100) . "\n\n";
    
    $dropStatements = [
        "DROP TRIGGER IF EXISTS trigger_detalle_cantidad_pendiente ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_detalle_insert_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_detalle_update_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_detalle_delete_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_col4_recalcula_saldo ON presupuesto_items CASCADE",
        "DROP TRIGGER IF EXISTS trigger_insert_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_update_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_delete_col4 ON detalle_certificados CASCADE",
        "DROP TRIGGER IF EXISTS trigger_recalcula_saldo ON presupuesto_items CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_detalle_cantidad_pendiente() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_detalle_insert_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_detalle_update_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_detalle_delete_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_col4_recalcula_saldo() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_insert_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_update_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_delete_col4() CASCADE",
        "DROP FUNCTION IF EXISTS fn_trigger_recalcula_saldo() CASCADE",
    ];
    
    foreach ($dropStatements as $stmt) {
        try {
            $pdo->exec($stmt);
            echo "  ✓ " . substr($stmt, 0, 60) . "...\n";
        } catch (Exception $e) {
            // Ignorar si no existen
        }
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "✅ VERIFICACIÓN - Triggers restantes:\n";
    echo str_repeat("=", 100) . "\n\n";
    
    $sql = "SELECT 
                trigger_name,
                event_object_table,
                event_manipulation
            FROM information_schema.triggers
            WHERE trigger_schema = 'public' 
            ORDER BY event_object_table, trigger_name";
    
    $stmt = $pdo->query($sql);
    $triggers = $stmt->fetchAll();
    
    if (empty($triggers)) {
        echo "✅ NO HAY TRIGGERS - Base de datos limpia\n";
    } else {
        foreach ($triggers as $trigger) {
            echo "  • " . $trigger['trigger_name'] . " (" . $trigger['event_manipulation'] . ")\n";
        }
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "✅ Eliminación completada\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
