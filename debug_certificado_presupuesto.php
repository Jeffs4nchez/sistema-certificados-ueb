<?php
/**
 * Script de DEBUG: Verificar sincronización certificado-presupuesto
 * 
 * Este script muestra:
 * 1. El certificado creado (CERT-001)
 * 2. Los items en detalle_certificados
 * 3. Los correspondientes items en presupuesto_items
 * 4. Verificación si el codigo_completo coincide
 */

require_once __DIR__ . '/bootstrap.php';

// Conexión a BD
try {
    // Usar la clase Database ya disponible en Bootstrap
    $db = Database::getInstance()->getConnection();
    
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    echo "║  DEBUG: Sincronización Certificado-Presupuesto            ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    // 1. CERTIFICADO CERT-001
    echo "📋 PASO 1: Obtener certificado CERT-001\n";
    echo "═══════════════════════════════════════\n";
    $stmt = $db->prepare("
        SELECT id, numero_certificado, monto_total, total_liquidado, total_pendiente, fecha_elaboracion 
        FROM certificados 
        WHERE numero_certificado = 'CERT-001' 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute();
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cert) {
        echo "✓ Certificado encontrado:\n";
        echo "  - ID: {$cert['id']}\n";
        echo "  - Número: {$cert['numero_certificado']}\n";
        echo "  - Monto Total: \${$cert['monto_total']}\n";
        echo "  - Total Liquidado: \${$cert['total_liquidado']}\n";
        echo "  - Total Pendiente: \${$cert['total_pendiente']}\n";
        echo "  - Fecha: {$cert['fecha_elaboracion']}\n\n";
        
        $certificado_id = $cert['id'];
        
        // 2. ITEMS EN detalle_certificados
        echo "📦 PASO 2: Items en detalle_certificados\n";
        echo "═══════════════════════════════════════\n";
        $stmt = $db->prepare("
            SELECT id, descripcion_item, monto, codigo_completo, cantidad_liquidacion 
            FROM detalle_certificados 
            WHERE certificado_id = ? 
            ORDER BY id
        ");
        $stmt->execute([$certificado_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($items)) {
            echo "✓ " . count($items) . " items encontrados:\n\n";
            foreach ($items as $idx => $item) {
                echo "  Item " . ($idx + 1) . ":\n";
                echo "    - ID: {$item['id']}\n";
                echo "    - Descripción: {$item['descripcion_item']}\n";
                echo "    - Monto: \${$item['monto']}\n";
                echo "    - Código Completo: '{$item['codigo_completo']}'\n";
                echo "    - Cantidad Liquidación: {$item['cantidad_liquidacion']}\n\n";
                
                // 3. BUSCAR ITEM EN presupuesto_items
                echo "    🔍 Buscando en presupuesto_items...\n";
                $stmtPres = $db->prepare("
                    SELECT id, col1, col3, col4, saldo_disponible, codigo_completo, descripciong1 
                    FROM presupuesto_items 
                    WHERE codigo_completo = ? 
                    LIMIT 1
                ");
                $stmtPres->execute([$item['codigo_completo']]);
                $presItem = $stmtPres->fetch(PDO::FETCH_ASSOC);
                
                if ($presItem) {
                    echo "      ✓ ENCONTRADO EN presupuesto_items:\n";
                    echo "        - ID: {$presItem['id']}\n";
                    echo "        - Descripción: {$presItem['descripciong1']}\n";
                    echo "        - col1 (Codificado): \${$presItem['col1']}\n";
                    echo "        - col3 (Disponible): \${$presItem['col3']}\n";
                    echo "        - col4 (Certificado): \${$presItem['col4']}\n";
                    echo "        - saldo_disponible: \${$presItem['saldo_disponible']}\n";
                    echo "        - Código Completo en BD: '{$presItem['codigo_completo']}'\n";
                    
                    // Verificar si col4 tiene el monto
                    if (floatval($presItem['col4']) >= floatval($item['monto'])) {
                        echo "      ✓ col4 ACTUALIZADO CORRECTAMENTE (contiene el monto)\n";
                    } else {
                        echo "      ❌ col4 NO ACTUALIZADO: debería tener \${$item['monto']} pero tiene \${$presItem['col4']}\n";
                    }
                } else {
                    echo "      ❌ NO ENCONTRADO EN presupuesto_items\n";
                    echo "      Buscando otros registros con código similar...\n";
                    
                    // Buscar por similitud
                    $searchTerm = '%' . str_replace(' ', '%', $item['codigo_completo']) . '%';
                    $stmtSearch = $db->prepare("
                        SELECT id, codigo_completo, descripciong1, col1, col3, col4 
                        FROM presupuesto_items 
                        WHERE codigo_completo LIKE ? OR descripciong1 LIKE ?
                        LIMIT 5
                    ");
                    $stmtSearch->execute([$searchTerm, $searchTerm]);
                    $similar = $stmtSearch->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($similar)) {
                        echo "      Registros similares encontrados:\n";
                        foreach ($similar as $sim) {
                            echo "        - codigo_completo: '{$sim['codigo_completo']}'\n";
                            echo "          descripciong1: '{$sim['descripciong1']}'\n";
                            echo "          col1: \${$sim['col1']}, col3: \${$sim['col3']}, col4: \${$sim['col4']}\n";
                        }
                    } else {
                        echo "      No se encontraron registros similares en presupuesto_items\n";
                    }
                }
                echo "\n";
            }
        } else {
            echo "❌ No se encontraron items en detalle_certificados\n";
        }
        
    } else {
        echo "❌ Certificado CERT-001 no encontrado\n";
    }
    
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    echo "║  Resumen de Verificación                                  ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n";
    echo "\n✓ Debug completado\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
