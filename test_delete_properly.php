<?php
require_once __DIR__ . '/bootstrap.php';

// Usar el modelo Certificate para crear correctamente
require_once __DIR__ . '/app/models/Certificate.php';

$certificateModel = new Certificate();
$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST: Crear, Verificar y Borrar Certificado (Correcta) ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // PASO 1: Crear certificado usando el modelo
    echo "📌 PASO 1: Crear certificado de test...\n";
    
    $cert_data = [
        'numero_certificado' => 'TEST-' . date('YmdHis'),
        'institucion' => 'TEST INSTITUCIÓN',
        'seccion_memorando' => '001',
        'descripcion' => 'Certificado de prueba',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 1500,
        'unid_ejecutora' => 'UEB',
        'unid_desc' => 'Unidad de Prueba',
        'clase_registro' => 'REG',
        'clase_gasto' => 'GAS',
        'tipo_doc_respaldo' => 'DOC',
        'clase_doc_respaldo' => 'CLF',
        'usuario_id' => 1,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $certificateModel->createCertificate($cert_data);
    echo "  ✓ Certificado creado: ID=$cert_id, Número={$cert_data['numero_certificado']}\n\n";
    
    // PASO 2: Agregar items
    echo "📌 PASO 2: Agregar items...\n";
    
    $items = [
        ['programa_codigo' => '01', 'subprograma_codigo' => '00', 'proyecto_codigo' => '000', 
         'actividad_codigo' => '001', 'item_codigo' => '510203', 'ubicacion_codigo' => '0200', 
         'fuente_codigo' => '001', 'organismo_codigo' => '', 'naturaleza_codigo' => '', 
         'item_descripcion' => 'Décimo Tercer Sueldo', 'monto' => 1000, 
         'codigo_completo' => '01 00 000 001 001 0200 510203', 'certificado_id' => $cert_id],
        
        ['programa_codigo' => '01', 'subprograma_codigo' => '00', 'proyecto_codigo' => '000', 
         'actividad_codigo' => '001', 'item_codigo' => '510602', 'ubicacion_codigo' => '0200', 
         'fuente_codigo' => '001', 'organismo_codigo' => '', 'naturaleza_codigo' => '', 
         'item_descripcion' => 'Fondo de Reserva', 'monto' => 500, 
         'codigo_completo' => '01 00 000 001 001 0200 510602', 'certificado_id' => $cert_id]
    ];
    
    $codigos = [];
    foreach ($items as $item) {
        $certificateModel->createDetail($item);
        echo "  ✓ Item: {$item['codigo_completo']} - \${$item['monto']}\n";
        $codigos[] = $item['codigo_completo'];
    }
    
    // PASO 3: Verificar col4 ANTES de borrar
    echo "\n📌 PASO 3: Verificar col4 ANTES de borrar certificado:\n";
    
    $col4_antes = [];
    foreach ($codigos as $cod) {
        $stmt = $db->prepare("SELECT col4, col3, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
        $stmt->execute([$cod]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pres) {
            $col4_antes[$cod] = floatval($pres['col4']);
            echo "  {$cod}: col4=\${$pres['col4']}, col3=\${$pres['col3']}, saldo=\${$pres['saldo_disponible']}\n";
        }
    }
    
    // PASO 4: BORRAR certificado
    echo "\n📌 PASO 4: BORRANDO certificado ID=$cert_id...\n";
    $stmt = $db->prepare("DELETE FROM certificados WHERE id = ?");
    $stmt->execute([$cert_id]);
    echo "  ✓ Certificado borrado\n";
    
    // PASO 5: Verificar col4 DESPUÉS de borrar
    echo "\n📌 PASO 5: Verificar col4 DESPUÉS de borrar certificado:\n\n";
    
    $problema = false;
    foreach ($codigos as $cod) {
        $stmt = $db->prepare("SELECT col4, col3, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
        $stmt->execute([$cod]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pres) {
            $col4_despues = floatval($pres['col4']);
            $col4_antes_val = $col4_antes[$cod] ?? 0;
            
            echo "  {$cod}:\n";
            echo "    ANTES:  col4=\${$col4_antes_val}\n";
            echo "    AHORA:  col4=\${$col4_despues}, col3=\${$pres['col3']}, saldo=\${$pres['saldo_disponible']}\n";
            
            if ($col4_despues > 0) {
                echo "    ❌ PROBLEMA: col4 NO se redujo a 0\n";
                $problema = true;
            } else {
                echo "    ✓ col4 correctamente reducido\n";
            }
        }
    }
    
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    if ($problema) {
        echo "║  ⚠️  PROBLEMA ENCONTRADO: col4 NO se reduce al borrar    ║\n";
    } else {
        echo "║  ✓ Todo funcionó correctamente                         ║\n";
    }
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
