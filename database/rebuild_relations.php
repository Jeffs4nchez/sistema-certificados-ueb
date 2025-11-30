<?php
/**
 * Script para reconstruir relaciones FK entre fuentes_financiamiento, ubicaciones e items
 */

require_once __DIR__ . '/../app/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "[INFO] Analizando estructura de presupuesto_items...\n";
    
    // Obtener estadísticas
    $stmt = $db->query('SELECT COUNT(DISTINCT codigog4) as count_fuentes FROM presupuesto_items WHERE codigog4 IS NOT NULL');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalFuentes = $result['count_fuentes'] ?? 0;
    
    echo "✓ Fuentes únicas en presupuesto_items: $totalFuentes\n\n";
    
    // Obtener ejemplos
    echo "[INFO] Estructura: codigog4 (Fuente) → Items\n";
    $stmt = $db->query('SELECT DISTINCT codigog4 FROM presupuesto_items WHERE codigog4 IS NOT NULL ORDER BY codigog4 LIMIT 10');
    $fuentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($fuentes as $fuente) {
        $codigo = $fuente['codigog4'];
        
        // Contar items de esta fuente
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM presupuesto_items WHERE codigog4 = ?');
        $stmt->execute([$codigo]);
        $itemCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "  Fuente: $codigo → $itemCount items\n";
    }
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
?>
