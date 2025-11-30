<?php
/**
 * Script para limpiar las tablas vacías y permitir nuevo import
 */

require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "[INFO] Limpiando tablas...\n";
    
    // Limpiar en orden inverso de FKs
    $tables = [
        'detalle_certificados',
        'items',
        'ubicaciones', 
        'fuentes_financiamiento',
        'organismos',
        'naturaleza_prestacion',
        'actividades',
        'proyectos',
        'subprogramas',
        'programas',
        'presupuesto_items'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'] ?? 0;
            
            if ($count > 0) {
                $db->exec("DELETE FROM $table");
                echo "✓ Limpiada: $table ($count registros eliminados)\n";
            } else {
                echo "✓ $table estaba vacía\n";
            }
        } catch (Exception $e) {
            echo "⚠ Error limpiando $table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n[SUCCESS] Base de datos limpiada. Puedes subir el CSV de nuevo.\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
?>
