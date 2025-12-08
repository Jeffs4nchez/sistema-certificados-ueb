<?php
/**
 * Diagnosticar: Ver qué está pasando con col4 en todos los items
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 120) . "\n";
echo "DIAGNOSTICAR: Col4 en todos los items\n";
echo str_repeat("=", 120) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Ver todos los items con su correspondencia en presupuesto
    $stmt = $db->query("
        SELECT 
            d.id,
            d.certificado_id,
            d.codigo_completo,
            d.monto,
            d.cantidad_liquidacion,
            d.cantidad_pendiente,
            p.id as presupuesto_id,
            p.col4,
            p.col1 as presupuesto_monto
        FROM detalle_certificados d
        LEFT JOIN presupuesto_items p ON p.codigo_completo = d.codigo_completo
        ORDER BY d.codigo_completo, d.id
    ");
    
    $items = $stmt->fetchAll();
    
    echo "ID | Cert | Código Completo | Monto | Liquidado | Pendiente | Presupuesto ID | Col4 | Análisis\n";
    echo str_repeat("-", 120) . "\n";
    
    $analisis_por_codigo = [];
    
    foreach ($items as $item) {
        $codigo = $item['codigo_completo'];
        
        // Inicializar análisis para este código
        if (!isset($analisis_por_codigo[$codigo])) {
            $analisis_por_codigo[$codigo] = [
                'items_count' => 0,
                'suma_monto' => 0,
                'suma_liquidacion' => 0,
                'suma_pendiente' => 0,
                'col4_presupuesto' => 0,
                'items' => []
            ];
        }
        
        $analisis_por_codigo[$codigo]['items_count']++;
        $analisis_por_codigo[$codigo]['suma_monto'] += $item['monto'];
        $analisis_por_codigo[$codigo]['suma_liquidacion'] += $item['cantidad_liquidacion'];
        $analisis_por_codigo[$codigo]['suma_pendiente'] += $item['cantidad_pendiente'];
        $analisis_por_codigo[$codigo]['col4_presupuesto'] = $item['col4'];
        $analisis_por_codigo[$codigo]['items'][] = [
            'id' => $item['id'],
            'monto' => $item['monto'],
            'pendiente' => $item['cantidad_pendiente']
        ];
        
        $analisis = "";
        if ($item['col4'] !== null) {
            $esperado_col4 = $item['monto'] - $item['cantidad_pendiente'];
            if ($item['col4'] == $esperado_col4) {
                $analisis = "✅ OK";
            } else {
                $analisis = "❌ " . number_format($item['col4'], 0) . " (debería ser " . number_format($esperado_col4, 0) . ")";
            }
        }
        
        printf("%3d | %4d | %-15s | %6.0f | %9.0f | %9.0f | %14s | %7.0f | %s\n",
            $item['id'],
            $item['certificado_id'],
            substr($codigo, 0, 15),
            $item['monto'],
            $item['cantidad_liquidacion'],
            $item['cantidad_pendiente'],
            $item['presupuesto_id'] ?? 'NULL',
            $item['col4'] ?? 0,
            $analisis
        );
    }
    
    echo "\n" . str_repeat("=", 120) . "\n";
    echo "ANÁLISIS POR CÓDIGO PRESUPUESTARIO\n";
    echo str_repeat("=", 120) . "\n\n";
    
    echo "Código | Items | Suma Monto | Suma Liq | Suma Pend | Col4 Presup | Esperado Col4 | ¿Correcto?\n";
    echo str_repeat("-", 120) . "\n";
    
    foreach ($analisis_por_codigo as $codigo => $analisis) {
        $esperado = $analisis['suma_monto'] - $analisis['suma_pendiente'];
        $correcto = ($analisis['col4_presupuesto'] == $esperado) ? "✅" : "❌";
        
        printf("%-6s | %5d | %10.0f | %8.0f | %9.0f | %11.0f | %13.0f | %s\n",
            substr($codigo, 0, 6),
            $analisis['items_count'],
            $analisis['suma_monto'],
            $analisis['suma_liquidacion'],
            $analisis['suma_pendiente'],
            $analisis['col4_presupuesto'],
            $esperado,
            $correcto
        );
        
        if ($analisis['col4_presupuesto'] != $esperado) {
            echo "       DETALLES DE ITEMS:\n";
            foreach ($analisis['items'] as $item) {
                echo "       Item ID {$item['id']}: Monto {$item['monto']}, Pendiente {$item['pendiente']}\n";
            }
        }
    }
    
    echo "\n" . str_repeat("=", 120) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
