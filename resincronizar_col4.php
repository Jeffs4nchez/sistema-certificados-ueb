<?php
/**
 * Resincronizar col4 = monto original de certificados
 */

require_once __DIR__ . '/app/Database.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔄 RESINCRONIZANDO COL4 CON MONTOS CERTIFICADOS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$db = Database::getInstance()->getConnection();

// Buscar todos los presupuestos que tienen certificados
$query = "
SELECT 
    pi.id,
    pi.codigo_completo,
    pi.col4 as col4_actual,
    SUM(dc.monto) as col4_correcto
FROM presupuesto_items pi
LEFT JOIN detalle_certificados dc ON pi.codigo_completo = dc.codigo_completo
WHERE dc.codigo_completo IS NOT NULL
GROUP BY pi.id, pi.codigo_completo, pi.col4
HAVING SUM(dc.monto) != pi.col4
ORDER BY pi.codigo_completo
";

try {
    echo "1️⃣  Buscando desincronizaciones...\n\n";
    $stmt = $db->query($query);
    $desincronizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($desincronizaciones)) {
        echo "✅ No hay desincronizaciones. Todo está correcto.\n\n";
    } else {
        echo "⚠️  Encontradas " . count($desincronizaciones) . " desincronizaciones:\n\n";
        
        foreach ($desincronizaciones as $item) {
            echo "Código: " . $item['codigo_completo'] . "\n";
            echo "  Col4 actual: \$" . number_format($item['col4_actual'], 2) . "\n";
            echo "  Col4 correcto: \$" . number_format($item['col4_correcto'], 2) . "\n\n";
        }
        
        echo "2️⃣  Corrigiendo col4...\n\n";
        
        $actualizados = 0;
        $errores = 0;
        
        foreach ($desincronizaciones as $item) {
            try {
                $id = $item['id'];
                $col4_correcto = floatval($item['col4_correcto']);
                
                $update_query = "UPDATE presupuesto_items SET col4 = ?, fecha_actualizacion = NOW() WHERE id = ?";
                $stmt_update = $db->prepare($update_query);
                $stmt_update->execute([$col4_correcto, $id]);
                
                echo "✅ " . $item['codigo_completo'] . " - Corregido a \$" . number_format($col4_correcto, 2) . "\n";
                $actualizados++;
            } catch (Exception $e) {
                echo "❌ " . $item['codigo_completo'] . " - Error: " . $e->getMessage() . "\n";
                $errores++;
            }
        }
        
        echo "\n═══════════════════════════════════════════════════════════════\n";
        echo "✅ RESINCRONIZACIÓN COMPLETADA\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "Actualizados: $actualizados\n";
        echo "Errores: $errores\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>
