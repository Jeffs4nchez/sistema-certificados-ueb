<?php
/**
 * Script para crear un certificado de prueba con 3 items
 * Un certificado con:
 * - Item 1: $1000
 * - Item 2: $500
 * - Item 3: $300
 * Total: $1800
 */

require_once __DIR__ . '/../app/Database.php';

echo "\n";
echo "============================================\n";
echo "  CREANDO CERTIFICADO DE PRUEBA CON 3 ITEMS\n";
echo "============================================\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Leer el script SQL
    $sql = file_get_contents(__DIR__ . '/crear_certificados_prueba.sql');
    
    // Ejecutar el SQL
    $pdo->exec($sql);
    
    echo "✅ Certificado creado exitosamente\n\n";
    
    // Mostrar el certificado y sus detalles
    echo "📊 CERTIFICADO CREADO:\n\n";
    
    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.numero_certificado,
            c.monto_total,
            COUNT(dc.id) as num_items
        FROM certificados c
        LEFT JOIN detalle_certificados dc ON dc.certificado_id = c.id
        WHERE c.numero_certificado = 'CERT-PRUEBA-001'
        GROUP BY c.id, c.numero_certificado, c.monto_total
    ");
    
    $certificado = $stmt->fetch();
    
    if ($certificado) {
        echo "🆔 " . $certificado['numero_certificado'] . "\n";
        echo "   💰 Monto Total: \$" . number_format($certificado['monto_total'], 2, ',', '.') . "\n";
        echo "   📦 Items: " . $certificado['num_items'] . "\n";
        echo "   🔗 Certificado ID: " . $certificado['id'] . "\n";
        echo "\n";
        
        // Mostrar los detalles
        echo "📋 DETALLES DEL CERTIFICADO:\n\n";
        
        $stmt_details = $pdo->query("
            SELECT id, descripcion_item, monto FROM detalle_certificados 
            WHERE certificado_id = " . $certificado['id'] . "
            ORDER BY id ASC
        ");
        
        $detalles = $stmt_details->fetchAll();
        
        foreach ($detalles as $idx => $detail) {
            echo "   Item " . ($idx + 1) . ":\n";
            echo "      ID: " . $detail['id'] . "\n";
            echo "      Descripción: " . $detail['descripcion_item'] . "\n";
            echo "      Monto: \$" . number_format($detail['monto'], 2, ',', '.') . "\n";
            echo "\n";
        }
    }
    
    echo "============================================\n";
    echo "  ✨ PRÓXIMOS PASOS\n";
    echo "============================================\n\n";
    echo "Ahora puedes liquidar los items de este certificado:\n\n";
    echo "1️⃣  VER LIQUIDACIONES DE UN ITEM:\n";
    echo "   GET /api/detalles/{detalle_id}/liquidaciones\n\n";
    echo "2️⃣  CREAR UNA LIQUIDACIÓN PARA UN ITEM:\n";
    echo "   POST /api/liquidaciones\n";
    echo "   Body: {\n";
    echo "     'detalle_certificado_id': ID_DEL_DETALLE,\n";
    echo "     'cantidad_liquidacion': 300.00,\n";
    echo "     'descripcion': 'Primera liquidación'\n";
    echo "   }\n\n";
    echo "3️⃣  EJEMPLO COMPLETO:\n";
    echo "   - Item 1 (ID: X): Liquidar \$500 de \$1000\n";
    echo "   - Item 2 (ID: Y): Liquidar \$250 de \$500\n";
    echo "   - Item 3 (ID: Z): Liquidar \$150 de \$300\n";
    echo "   Total liquidado: \$900 de \$1800\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>
