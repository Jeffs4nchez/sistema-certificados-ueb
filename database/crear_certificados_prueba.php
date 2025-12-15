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
        'institucion' => 'Universidad Estatal de Bolívar',
        'descripcion' => 'Certificado de prueba - Item 1000',
        'monto_item' => 1000.00
    ],
    [
        'numero' => 'CERT-PRUEBA-002',
        'institucion' => 'Universidad Estatal de Bolívar',
        'descripcion' => 'Certificado de prueba - Item 500',
        'monto_item' => 500.00
    ],
    [
        'numero' => 'CERT-PRUEBA-003',
        'institucion' => 'Universidad Estatal de Bolívar',
        'descripcion' => 'Certificado de prueba - Item 300',
        'monto_item' => 300.00
    ]
];

// IDs disponibles de presupuesto jerárquico (valores por defecto)
$programas = [1, 2, 3];
$items = [1, 2, 3];
$organismos = [1];
$naturalezas = [1, 2, 3];

try {
    foreach ($certificados_data as $index => $cert_data) {
        echo "📋 Creando certificado " . ($index + 1) . "...\n";
        
        // 1. INSERTAR CERTIFICADO
        $sql_cert = "INSERT INTO certificados 
                    (numero_certificado, institucion, descripcion, fecha_elaboracion, 
                     monto_total, estado, usuario_creacion)
                    VALUES (?, ?, ?, CURRENT_DATE, ?, 'PENDIENTE', 'SISTEMA')";
        
        $stmt = $pdo->prepare($sql_cert);
        $result = $stmt->execute([
            $cert_data['numero'],
            $cert_data['institucion'],
            $cert_data['descripcion'],
            $cert_data['monto_item']
        ]);
        
        if (!$result) {
            throw new Exception("Error al insertar certificado: " . print_r($stmt->errorInfo(), true));
        }
        
        $certificado_id = $pdo->lastInsertId();
        echo "   ✅ Certificado creado (ID: $certificado_id)\n";
        
        // 2. INSERTAR DETALLE DEL CERTIFICADO
        // Seleccionar valores random del presupuesto jerárquico
        $programa_id = $programas[array_rand($programas)];
        $item_id = $items[array_rand($items)];
        $organismo_id = $organismos[0];
        $naturaleza_id = $naturalezas[array_rand($naturalezas)];
        
        $sql_detalle = "INSERT INTO detalle_certificados 
                       (certificado_id, programa_id, item_id, organismo_id, naturaleza_id,
                        descripcion_item, monto)
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql_detalle);
        $result = $stmt->execute([
            $certificado_id,
            $programa_id,
            $item_id,
            $organismo_id,
            $naturaleza_id,
            'Item de prueba - Monto: $' . $cert_data['monto_item'],
            $cert_data['monto_item']
        ]);
        
        if (!$result) {
            throw new Exception("Error al insertar detalle: " . print_r($stmt->errorInfo(), true));
        }
        
        $detalle_id = $pdo->lastInsertId();
        echo "   ✅ Detalle creado (ID: $detalle_id)\n";
        echo "   💰 Monto: \$" . number_format($cert_data['monto_item'], 2, ',', '.') . "\n";
        echo "\n";
    }
    
    echo "============================================\n";
    echo "  ✅ CERTIFICADOS CREADOS CON ÉXITO\n";
    echo "============================================\n\n";
    
    // Mostrar resumen
    echo "📊 RESUMEN DE CERTIFICADOS CREADOS:\n\n";
    
    $sql = "SELECT c.id, c.numero_certificado, c.monto_total, 
                   COUNT(dc.id) as num_items
            FROM certificados c
            LEFT JOIN detalle_certificados dc ON dc.certificado_id = c.id
            WHERE c.numero_certificado LIKE 'CERT-' || date_trunc('day', CURRENT_DATE)::text || '%'
            GROUP BY c.id, c.numero_certificado, c.monto_total
            ORDER BY c.id DESC
            LIMIT 3";
    
    // Para PostgreSQL usamos una consulta distinta
    $sql = "SELECT c.id, c.numero_certificado, c.monto_total, 
                   COUNT(dc.id) as num_items
            FROM certificados c
            LEFT JOIN detalle_certificados dc ON dc.certificado_id = c.id
            WHERE c.numero_certificado LIKE 'CERT-%'
            GROUP BY c.id, c.numero_certificado, c.monto_total
            ORDER BY c.id DESC
            LIMIT 3";
    
    $stmt = $pdo->query($sql);
    $certificados = $stmt->fetchAll();
    
    foreach ($certificados as $cert) {
        echo "🆔 " . $cert['numero_certificado'] . "\n";
        echo "   💰 Monto: \$" . number_format($cert['monto_total'], 2, ',', '.') . "\n";
        echo "   📦 Items: " . $cert['num_items'] . "\n";
        echo "   🔗 URL: /certificados/" . $cert['id'] . "\n";
        echo "\n";
    }
    
    echo "✨ Ahora puedes liquidar estos certificados usando:\n";
    echo "   - POST /api/liquidaciones\n";
    echo "   - Parámetros: detalle_certificado_id, cantidad_liquidacion\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>
