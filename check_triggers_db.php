<?php
/**
 * Script para verificar triggers activos en la BD PostgreSQL
 */

// Credenciales de conexión
$host = 'localhost';
$port = '5432';
$user = 'postgres';
$pass = 'jeffo2003';
$database = 'certificados_sistema';

try {
    // Conectar a PostgreSQL
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
    
    echo "✅ Conexión exitosa a PostgreSQL\n";
    echo str_repeat("=", 80) . "\n";
    
    // Consultar todos los triggers
    $sql = "SELECT 
                trigger_name,
                event_object_table,
                event_manipulation,
                action_timing,
                action_statement
            FROM information_schema.triggers 
            WHERE trigger_schema = 'public' 
            ORDER BY event_object_table, trigger_name";
    
    $stmt = $pdo->query($sql);
    $triggers = $stmt->fetchAll();
    
    if (empty($triggers)) {
        echo "❌ No se encontraron triggers en la base de datos\n";
    } else {
        echo "📊 TRIGGERS ENCONTRADOS: " . count($triggers) . "\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $currentTable = null;
        foreach ($triggers as $trigger) {
            if ($currentTable !== $trigger['event_object_table']) {
                $currentTable = $trigger['event_object_table'];
                echo "\n🔴 TABLA: {$currentTable}\n";
                echo str_repeat("-", 80) . "\n";
            }
            
            echo sprintf(
                "  • %s (%s %s)\n",
                $trigger['trigger_name'],
                $trigger['action_timing'],
                $trigger['event_manipulation']
            );
        }
    }
    
    // Consultar funciones asociadas a triggers
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "🔧 FUNCIONES DE TRIGGERS:\n";
    echo str_repeat("=", 80) . "\n";
    
    $sql2 = "SELECT 
                p.proname,
                p.pronargs,
                pg_catalog.pg_get_functiondef(p.oid) as definition
            FROM pg_proc p
            JOIN pg_namespace n ON n.oid = p.pronamespace
            WHERE n.nspname = 'public' 
              AND p.proname LIKE 'fn_trigger%' OR p.proname LIKE 'trg_%'
            ORDER BY p.proname";
    
    $stmt2 = $pdo->query($sql2);
    $functions = $stmt2->fetchAll();
    
    if (!empty($functions)) {
        foreach ($functions as $func) {
            echo "\n✅ {$func['proname']} ({$func['pronargs']} args)\n";
            echo substr($func['definition'], 0, 150) . "...\n";
        }
    } else {
        echo "Sin funciones de trigger encontradas\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}
?>
