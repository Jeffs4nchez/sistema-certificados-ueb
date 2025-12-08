<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST: Borrar Certificado y Verificar col4              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Obtener el CERT-001 y sus items
    $stmt = $db->prepare("SELECT id FROM certificados WHERE numero_certificado = 'CERT-001' LIMIT 1");
    $stmt->execute();
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cert) {
        echo "❌ No se encontró CERT-001\n";
        exit;
    }
    
    $cert_id = $cert['id'];
    echo "📋 Certificado encontrado: ID=$cert_id\n\n";
    
    // Obtener items antes de borrar
    echo "ANTES DE BORRAR:\n";
    echo "═══════════════\n";
    $stmt = $db->prepare("
        SELECT id, codigo_completo, monto 
        FROM detalle_certificados 
        WHERE certificado_id = ?
    ");
    $stmt->execute([$cert_id]);
    $items_antes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $codigos = [];
    foreach ($items_antes as $item) {
        echo "  Item: {$item['codigo_completo']} - Monto: \${$item['monto']}\n";
        $codigos[] = $item['codigo_completo'];
    }
    
    // Verificar col4 antes
    echo "\n  Valores en presupuesto_items ANTES:\n";
    foreach ($codigos as $cod) {
        $stmt = $db->prepare("SELECT codigo_completo, col4, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
        $stmt->execute([$cod]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pres) {
            echo "    {$cod}: col4=\${$pres['col4']}, saldo=\${$pres['saldo_disponible']}\n";
        }
    }
    
    // BORRAR certificado
    echo "\n📌 BORRANDO certificado ID=$cert_id...\n";
    $stmt = $db->prepare("DELETE FROM certificados WHERE id = ?");
    $result = $stmt->execute([$cert_id]);
    
    if ($result) {
        echo "  ✓ Certificado borrado\n";
    } else {
        echo "  ❌ Error al borrar\n";
    }
    
    // Verificar col4 después
    echo "\n  Valores en presupuesto_items DESPUÉS:\n";
    foreach ($codigos as $cod) {
        $stmt = $db->prepare("SELECT codigo_completo, col4, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
        $stmt->execute([$cod]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pres) {
            echo "    {$cod}: col4=\${$pres['col4']}, saldo=\${$pres['saldo_disponible']}\n";
            
            // Verificar si debería ser 0
            if (floatval($pres['col4']) != 0) {
                echo "      ❌ PROBLEMA: col4 debería ser 0\n";
            } else {
                echo "      ✓ col4 correctamente en 0\n";
            }
        }
    }
    
    // Verificar si items fueron borrados
    echo "\nVerificación de borrado en cascade:\n";
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM detalle_certificados WHERE certificado_id = ?");
    $stmt->execute([$cert_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Items en detalle_certificados: {$result['cnt']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
