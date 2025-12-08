<?php
/**
 * Script para ver el detalle completo de todas las funciones de triggers
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
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    // Obtener todas las funciones de trigger con su código completo
    $sql = "SELECT 
                p.proname,
                pg_catalog.pg_get_functiondef(p.oid) as definition
            FROM pg_proc p
            JOIN pg_namespace n ON n.oid = p.pronamespace
            WHERE n.nspname = 'public' 
              AND (p.proname LIKE 'fn_trigger%' OR p.proname LIKE 'trg_%')
            ORDER BY p.proname";
    
    $stmt = $pdo->query($sql);
    $functions = $stmt->fetchAll();
    
    echo "📋 FUNCIONES DE TRIGGERS ACTIVAS EN LA BD\n";
    echo str_repeat("=", 100) . "\n\n";
    
    foreach ($functions as $index => $func) {
        echo ($index + 1) . ". " . strtoupper($func['proname']) . "\n";
        echo str_repeat("-", 100) . "\n";
        echo $func['definition'];
        echo "\n\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
