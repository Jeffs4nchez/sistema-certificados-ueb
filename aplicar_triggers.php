<?php
/**
 * Script para aplicar los triggers optimizados a la BD
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
    
    echo "🔧 Aplicando triggers optimizados...\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // Leer el archivo SQL
    $sqlFile = __DIR__ . '/database/triggers_optimizados.sql';
    if (!file_exists($sqlFile)) {
        die("❌ Archivo SQL no encontrado: $sqlFile\n");
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    // Dividir en statements individuales
    $statements = array_filter(
        array_map('trim', explode(';', $sqlContent)),
        function($stmt) {
            return !empty($stmt) && strpos($stmt, '--') !== 0;
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (strpos(trim($statement), '--') === 0 || empty(trim($statement))) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            echo "⚠️  Error en statement:\n";
            echo substr($statement, 0, 100) . "...\n";
            echo "Error: " . $e->getMessage() . "\n\n";
            $errorCount++;
        }
    }
    
    echo "✅ Resultados:\n";
    echo "   Statements ejecutados exitosamente: $successCount\n";
    echo "   Errores: $errorCount\n\n";
    
    // Verificar triggers creados
    echo "📊 TRIGGERS ACTIVOS AHORA:\n";
    echo str_repeat("=", 100) . "\n\n";
    
    $sql = "SELECT 
                trigger_name,
                event_object_table,
                event_manipulation,
                action_timing
            FROM information_schema.triggers
            WHERE trigger_schema = 'public' 
            ORDER BY event_object_table, trigger_name";
    
    $stmt = $pdo->query($sql);
    $triggers = $stmt->fetchAll();
    
    $currentTable = null;
    foreach ($triggers as $trigger) {
        if ($currentTable !== $trigger['event_object_table']) {
            $currentTable = $trigger['event_object_table'];
            echo "\n🔴 TABLA: {$currentTable}\n";
            echo str_repeat("-", 100) . "\n";
        }
        
        echo sprintf(
            "  ✓ %s (%s %s)\n",
            $trigger['trigger_name'],
            $trigger['action_timing'],
            $trigger['event_manipulation']
        );
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "✅ Triggers optimizados aplicados correctamente!\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}
?>
