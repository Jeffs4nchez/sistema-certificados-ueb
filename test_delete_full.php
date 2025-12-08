<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST COMPLETO: Crear, Verificar y Borrar Certificado  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // PASO 1: Crear un certificado de prueba
    echo "📌 PASO 1: Crear certificado de prueba...\n";
    
    $stmt = $db->prepare("
        INSERT INTO certificados (
            numero_certificado, institucion, usuario_id, fecha_elaboracion, 
            monto_total, total_liquidado, total_pendiente
        ) VALUES (?, ?, ?, NOW(), ?, 0, ?)
    ");
    
    $numero = 'TEST-' . date('YmdHis');
    $stmt->execute([$numero, 'TEST INSTITUCIÓN', 1, 1500, 1500]);
    $cert_id = $db->lastInsertId();
    
    echo "  ✓ Certificado creado: ID=$cert_id, Número=$numero\n\n";
    
    // PASO 2: Agregar items
    echo "📌 PASO 2: Agregar items al certificado...\n";
    
    $items_data = [
        ['01 00 000 001 001 0200 510203', 'Décimo Tercer Sueldo', 1000],
        ['01 00 000 001 001 0200 510602', 'Fondo de Reserva', 500]
    ];
    
    foreach ($items_data as $item) {
        $stmt = $db->prepare("
            INSERT INTO detalle_certificados (
                certificado_id, programa_codigo, subprograma_codigo, proyecto_codigo,
                actividad_codigo, item_codigo, ubicacion_codigo, fuente_codigo,
                descripcion_item, monto, codigo_completo
            ) VALUES (?, '01', '00', '000', '001', '510203', '0200', '001', ?, ?, ?)
        ");
        
        $stmt->execute([$cert_id, $item[1], $item[2], $item[0]]);
        echo "  ✓ Item agregado: {$item[0]} - \${$item[2]}\n";
    }
    
    echo "\nVerificando col4 en presupuesto_items DESPUÉS DE INSERTAR:\n";
    foreach ($items_data as $item) {
        $stmt = $db->prepare("SELECT codigo_completo, col4, col3, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
        $stmt->execute([$item[0]]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pres) {
            echo "  {$item[0]}:\n";
            echo "    col3: \${$pres['col3']}, col4: \${$pres['col4']}, saldo: \${$pres['saldo_disponible']}\n";
        }
    }
    
    // PASO 3: Borrar certificado
    echo "\n📌 PASO 3: Borrar certificado ID=$cert_id...\n";
    $stmt = $db->prepare("DELETE FROM certificados WHERE id = ?");
    $stmt->execute([$cert_id]);
    echo "  ✓ Certificado borrado\n";
    
    // PASO 4: Verificar col4 después de borrar
    echo "\nVerificando col4 en presupuesto_items DESPUÉS DE BORRAR:\n";
    $col4_no_se_resto = false;
    
    foreach ($items_data as $item) {
        $stmt = $db->prepare("SELECT codigo_completo, col4, col3, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
        $stmt->execute([$item[0]]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pres) {
            echo "  {$item[0]}:\n";
            echo "    col3: \${$pres['col3']}, col4: \${$pres['col4']}, saldo: \${$pres['saldo_disponible']}\n";
            
            if (floatval($pres['col4']) > 0) {
                echo "    ❌ PROBLEMA: col4 debería ser 0 pero es \${$pres['col4']}\n";
                $col4_no_se_resto = true;
            } else {
                echo "    ✓ col4 correctamente restituido a 0\n";
            }
        }
    }
    
    if ($col4_no_se_resto) {
        echo "\n⚠️  PROBLEMA IDENTIFICADO: col4 NO se resta cuando se borran items vía DELETE de certificados\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
