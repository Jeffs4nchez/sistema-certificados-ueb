<?php
/**
 * Script para ejecutar el SQL de migración de year
 * Accede a: http://localhost/programas/certificados-sistema/execute_sql.php
 */

require_once __DIR__ . '/app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>Ejecutando Script SQL...</h2>";
    echo "<pre>";
    
    // 1. Add year column to certificados
    echo "1. Agregando columna 'year' a certificados...\n";
    try {
        $db->exec("ALTER TABLE certificados ADD COLUMN year INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE)");
        echo "   ✓ Columna agregada\n";
    } catch (Exception $e) {
        echo "   ℹ Columna ya existe o error: " . $e->getMessage() . "\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_certificados_year ON certificados(year)");
        echo "   ✓ Índice creado\n";
    } catch (Exception $e) {
        echo "   ℹ Índice ya existe: " . $e->getMessage() . "\n";
    }
    
    // 2. Add year column to detalle_certificados
    echo "\n2. Agregando columna 'year' a detalle_certificados...\n";
    try {
        $db->exec("ALTER TABLE detalle_certificados ADD COLUMN year INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE)");
        echo "   ✓ Columna agregada\n";
    } catch (Exception $e) {
        echo "   ℹ Columna ya existe o error: " . $e->getMessage() . "\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_detalle_certificados_year ON detalle_certificados(year)");
        echo "   ✓ Índice creado\n";
    } catch (Exception $e) {
        echo "   ℹ Índice ya existe: " . $e->getMessage() . "\n";
    }
    
    // 3. Add year column to presupuesto_items
    echo "\n3. Agregando columna 'year' a presupuesto_items...\n";
    try {
        $db->exec("ALTER TABLE presupuesto_items ADD COLUMN year INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE)");
        echo "   ✓ Columna agregada\n";
    } catch (Exception $e) {
        echo "   ℹ Columna ya existe o error: " . $e->getMessage() . "\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_presupuesto_items_year ON presupuesto_items(year)");
        echo "   ✓ Índice creado\n";
    } catch (Exception $e) {
        echo "   ℹ Índice ya existe: " . $e->getMessage() . "\n";
    }
    
    // 4. Add year column to estructura_presupuestaria
    echo "\n4. Agregando columna 'year' a estructura_presupuestaria...\n";
    try {
        $db->exec("ALTER TABLE estructura_presupuestaria ADD COLUMN year INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE)");
        echo "   ✓ Columna agregada\n";
    } catch (Exception $e) {
        echo "   ℹ Columna ya existe o error: " . $e->getMessage() . "\n";
    }
    
    try {
        $db->exec("CREATE INDEX idx_estructura_year ON estructura_presupuestaria(year)");
        echo "   ✓ Índice creado\n";
    } catch (Exception $e) {
        echo "   ℹ Índice ya existe: " . $e->getMessage() . "\n";
    }
    
    // 5. Update existing records
    echo "\n5. Poblando datos de 'year' en registros existentes...\n";
    
    $result = $db->exec("UPDATE certificados SET year = EXTRACT(YEAR FROM fecha_elaboracion) WHERE year IS NULL OR year = 0");
    echo "   ✓ Actualizados " . $result . " registros en certificados\n";
    
    $result = $db->exec("UPDATE detalle_certificados SET year = EXTRACT(YEAR FROM fecha_creacion) WHERE year IS NULL OR year = 0");
    echo "   ✓ Actualizados " . $result . " registros en detalle_certificados\n";
    
    $result = $db->exec("UPDATE presupuesto_items SET year = EXTRACT(YEAR FROM CURRENT_DATE) WHERE year IS NULL OR year = 0");
    echo "   ✓ Actualizados " . $result . " registros en presupuesto_items\n";
    
    $result = $db->exec("UPDATE estructura_presupuestaria SET year = EXTRACT(YEAR FROM CURRENT_DATE) WHERE year IS NULL OR year = 0");
    echo "   ✓ Actualizados " . $result . " registros en estructura_presupuestaria\n";
    
    // Verify
    echo "\n6. Verificando datos...\n";
    $stmt = $db->query("SELECT COUNT(*) as total, year FROM certificados GROUP BY year ORDER BY year DESC");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Certificados por año:\n";
    foreach ($results as $row) {
        echo "   - Año " . $row['year'] . ": " . $row['total'] . " registros\n";
    }
    
    echo "\n</pre>";
    echo "<h2 style='color: green;'>✓ ¡Script ejecutado exitosamente!</h2>";
    echo "<p><a href='index.php'>Volver a la aplicación</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error al ejecutar el script:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
