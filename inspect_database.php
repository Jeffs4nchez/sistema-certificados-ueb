<?php
/**
 * Script para inspeccionar la estructura completa de la BD PostgreSQL
 */

require_once __DIR__ . '/bootstrap.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== INSPECCIÓN COMPLETA DE BASE DE DATOS PostgreSQL ===\n\n";
    
    // 1. Obtener todas las tablas
    $stmt = $db->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    echo "TABLAS ENCONTRADAS: " . count($tables) . "\n";
    echo str_repeat("=", 80) . "\n\n";
    
    foreach ($tables as $t) {
        $tableName = $t['table_name'];
        echo "TABLE: {$tableName}\n";
        echo str_repeat("-", 80) . "\n";
        
        // Obtener columnas de cada tabla
        $colStmt = $db->prepare("
            SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = ? 
            ORDER BY ordinal_position
        ");
        $colStmt->execute([$tableName]);
        $columns = $colStmt->fetchAll();
        
        echo "Columnas:\n";
        foreach ($columns as $col) {
            $nullable = $col['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $col['column_default'] ? " DEFAULT {$col['column_default']}" : '';
            echo sprintf("  %-30s %-20s %-10s%s\n", 
                $col['column_name'], 
                $col['data_type'], 
                $nullable,
                $default
            );
        }
        
        // Obtener cantidad de registros
        $countStmt = $db->query("SELECT COUNT(*) as cnt FROM {$tableName}");
        $count = $countStmt->fetch();
        echo "\nRegistros: " . $count['cnt'] . "\n";
        
        // Mostrar primeros registros si hay datos
        if ($count['cnt'] > 0) {
            echo "\nPrimeros registros:\n";
            $dataStmt = $db->query("SELECT * FROM {$tableName} LIMIT 3");
            $rows = $dataStmt->fetchAll();
            foreach ($rows as $row) {
                echo "  " . json_encode($row) . "\n";
            }
        }
        
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if ($e instanceof PDOException) {
        echo "SQL Error: " . $e->errorInfo[2] . "\n";
    }
}
?>
