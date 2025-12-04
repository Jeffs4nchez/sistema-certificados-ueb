<?php
/**
 * Script para ejecutar la limpieza de triggers directamente
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'postgres');
define('DB_PASS', 'jeffo2003');
define('DB_NAME', 'certificados_sistema');
define('DB_PORT', '5432');

try {
    $conn = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS);
    
    if (!$conn) {
        die("[ERROR] No se pudo conectar: " . pg_last_error());
    }
    
    echo "[✓] Conectado a PostgreSQL\n\n";
    
    // Leer el archivo SQL
    $sql_file = __DIR__ . '/limpiar_triggers.sql';
    if (!file_exists($sql_file)) {
        die("[ERROR] Archivo no encontrado: $sql_file");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Dividir por punto y coma y ejecutar cada comando
    $statements = explode(';', $sql_content);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        if (empty($statement)) {
            continue;
        }
        
        echo "Ejecutando: " . substr($statement, 0, 50) . "...\n";
        
        $result = pg_query($conn, $statement . ';');
        
        if ($result) {
            echo "  ✓ OK\n";
        } else {
            echo "  ⚠ " . pg_last_error($conn) . "\n";
        }
    }
    
    echo "\n[✓] Limpieza completada\n";
    
    pg_close($conn);
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage();
}
?>
