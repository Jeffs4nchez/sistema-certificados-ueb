<?php
/**
 * Script de migración para agregar columnas FK faltantes
 */

require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Agregar actividad_id a fuentes_financiamiento si no existe
    echo "[INFO] Verificando columna actividad_id en fuentes_financiamiento...\n";
    
    try {
        $stmt = $db->query("SELECT actividad_id FROM fuentes_financiamiento LIMIT 1");
        echo "✓ Columna actividad_id ya existe en fuentes_financiamiento\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'actividad_id') !== false || strpos($e->getMessage(), 'column') !== false) {
            echo "Agregando columna actividad_id a fuentes_financiamiento...\n";
            $db->exec("ALTER TABLE fuentes_financiamiento ADD COLUMN actividad_id INTEGER REFERENCES actividades(id) ON DELETE SET NULL");
            echo "✓ Columna actividad_id agregada\n";
        } else {
            throw $e;
        }
    }
    
    // 2. Agregar fuente_id a ubicaciones si no existe
    echo "[INFO] Verificando columna fuente_id en ubicaciones...\n";
    
    try {
        $stmt = $db->query("SELECT fuente_id FROM ubicaciones LIMIT 1");
        echo "✓ Columna fuente_id ya existe en ubicaciones\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'fuente_id') !== false || strpos($e->getMessage(), 'column') !== false) {
            echo "Agregando columna fuente_id a ubicaciones...\n";
            $db->exec("ALTER TABLE ubicaciones ADD COLUMN fuente_id INTEGER REFERENCES fuentes_financiamiento(id) ON DELETE SET NULL");
            echo "✓ Columna fuente_id agregada\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n[SUCCESS] Migración completada\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
?>
